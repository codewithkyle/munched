# Event Bus

Additional details about the [Publish-subscribe software architecture pattern](https://en.wikipedia.org/wiki/Publish%E2%80%93subscribe_pattern).

## Create Subscription

```javascript
const ticket = EventBus.create();
```

## Create Custom Subscription

```javascript
const ticket = "my-unique-id";
EventBus.create(ticket);
```

## Subscribe

```typescript
const inbox = (data:any) => {};
const inboxId = EventBus.subscribe(ticket, inbox);
```

## Unsubscribe

```javascript
EventBus.unsubscribe(inboxId);
EventBus.unsubscribe(inboxId, ticket);
```

## Destroy Subscription

```javascript
EventBus.destory(ticket);
```