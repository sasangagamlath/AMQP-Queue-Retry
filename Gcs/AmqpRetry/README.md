# Gcs_AmqpRetry

Adobe Commerce / Magento 2 module that adds configurable, automatic retry
handling for failed AMQP message queue consumers. Failed messages are
routed through a series of time delayed RabbitMQ wait queues before
eventually falling through to the standard Dead Letter Queue (DLQ) behavior.

Retry scheduling is configurable per queue, via the admin UI or CLI. The
implementation relies entirely on RabbitMQ's native TTL and Dead Letter
Exchange (DLX) features there are no external schedulers, cron jobs, or
polling involved.

## Features

- Automatically retries failed consumer messages a configurable number of
  times, with configurable per attempt delays.
- Supports a separate, longer interval retry schedule for the DLQ phase,
  once the initial retries are exhausted.
- Requires **zero changes** to existing consumers.
- Retry state is kept entirely in the AMQP message's `application_headers`
  — no database row is written per message.
- Operators can create/delete wait queue topology on the live broker at
  runtime, without a deployment.

### Non goals

- Does not implement AMQP consumers themselves.
- Does not support retry for non AMQP (e.g. MySQL backed) queue consumers.
- Configuration is per **queue**, not per message or per topic.

## Requirements

- PHP 8.3 – 8.4
- Adobe Commerce / Magento 2 with `Magento_MQFramework` and `Magento_Amqp`
  enabled
- A running RabbitMQ broker configured as Magento's AMQP connection

## Installation

```bash
composer require gcs/module-amqp-retry
bin/magento module:enable Gcs_AmqpRetry
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

`setup:upgrade` creates the `amqp_retry_queue_config` table that stores
per queue retry configuration.

## Configuration

### Global defaults

Go to **Stores → Configuration → GCS Extensions → GCS AMQP Retry
Configuration** to set the store wide defaults used when a queue doesn't
have its own explicit configuration:

| Field | Description | Default |
|---|---|---|
| Default Initial Attempt Step Delays | Comma separated seconds for initial retries | `60,300,900` |
| Default DLQ Intervals | Comma separated seconds for DLQ phase retries | `3600,86400` |
| Exchange Name | Default exchange used when publishing retry messages | `magento` |

### Per queue configuration

Go to **System → AMQP Retry → Queue Retry Configurations** to manage
individual queues:

| Field | Description |
|---|---|
| Queue Name | The AMQP queue this configuration applies to (populated from `queue_consumer.xml`) |
| Exchange Name | Exchange used when publishing retry messages for this queue |
| Is Enabled | Whether retry handling is active for this queue |
| Initial Intervals | Comma separated delay seconds for the initial retry phase, e.g. `60,300,900` |
| DLQ Intervals | Comma separated delay seconds for the DLQ retry phase, e.g. `3600,86400` |
| Topology Applied At | Timestamp of the last successful "Apply Topology" run |

Grid row actions: **Edit**, **Apply Topology**, **Delete Topology**,
**Delete Config**. Mass delete and full text search by queue name are also
available.

## How it works

**Normal flow (no retries):**

```
Producer → Exchange → Main Queue → Consumer (success) → ACK
                                  → Consumer (failure) → NACK / Reject → discarded / RabbitMQ DLX
```

**Retry flow (with this module):**

1. A consumer throws an exception and `Queue::reject()` is called.
2. An `aroundReject` plugin intercepts the call and looks up retry
   configuration for the queue.
3. If no configuration exists, or it's disabled, the original `reject()`
   behavior runs unchanged.
4. Otherwise, the plugin reads the `x-retry-count` header (default `0`),
   determines which interval list applies (initial vs. DLQ vs. exhausted),
   increments the header, and republishes the message to a wait queue for
   that attempt's delay then ACKs the original message so it leaves the
   main queue.
5. Once all initial + DLQ intervals are exhausted, the plugin falls through
   to the original `reject()` behavior.

**Wait queue topology:** for each configured delay `D` seconds, a queue
named `{queue_name}.wait.{D}s` is declared with:

- `x-message-ttl`: `D * 1000` ms
- `x-dead-letter-exchange`: `""` (default exchange)
- `x-dead-letter-routing-key`: the original queue's name

When a message's TTL expires in a wait queue, RabbitMQ automatically
routes it back to the main queue via the DLX, and the consumer picks it up
again. No consumer ever reads directly from a wait queue.

**Example:** a queue configured with `initial_intervals = [60, 300]` and
`dlq_intervals = [3600]` gets a maximum of 4 total delivery attempts
(1 original + 2 initial retries + 1 DLQ retry) before falling through to
normal reject behavior.

## Applying topology

Before retries can work, the wait queues must exist on the broker. Do this
either from the admin grid ("Apply Topology" row action) or via CLI.

## CLI commands

| Command | Description |
|---|---|
| `queue:retry:apply-topology [--queue\|-u <name>]` | Run wait queues on the broker for the given queue. |
| `queue:retry:delete-topology [--queue\|-u <name>]` | Deletes wait queues for the given queue. |
| `queue:retry:apply-topology`  | Applies the retry topology for all enabled queues. |
| `queue:retry:delete-topology` | Deletes the retry topology for all configured queues. |
| `queue:retry:list` | Displays a table of all configured queues, their retry intervals, and the last topology applied timestamp.. |

## Example
`bin/magento queue:retry:apply-topology -u product_action_attribute.update`

`bin/magento queue:retry:delete-topology -u product_action_attribute.update`

## Message headers

| Header | Type | Description |
|---|---|---|
| `x-retry-count` | int | Number of times the message has been retried. Absent/`0` on first delivery; incremented by the plugin before each republish. |

No other state is tracked — nothing about in flight retries is stored in
the database.

## Setup checklist

1. `bin/magento setup:upgrade`
2. `bin/magento setup:di:compile`
3. Configure at least one queue under **System → AMQP Retry → Queue Retry
   Configurations**.
4. Run `bin/magento queue:retry:apply-topology` (or use the admin grid) to
   create the wait queues in RabbitMQ.
5. Start/restart your consumers no consumer code changes are required.

## Error handling

- If the module can't connect to RabbitMQ while applying/deleting
  topology, a descriptive exception is thrown; the CLI commands and admin
  controller surface that message rather than failing silently.
- If a wait queue doesn't exist yet (topology not applied) when a retry
  publish is attempted, the failure is not swallowed it propagates so the
  original message is not accidentally ACKed without being safely
  re queued. Run `queue:retry:apply-topology` for the affected queue.

