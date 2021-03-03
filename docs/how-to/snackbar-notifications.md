# Snackbar Notifications

For technical information read the [snackbar notifications reference document](/references/snackbar-notifications).

## Blazor

### Success

```csharp
await JSRuntime.InvokeVoidAsync("Notify", "Notification message.");
```

## JavaScript

### Success

```javascript
Notify("Notificaiton message.");
```
