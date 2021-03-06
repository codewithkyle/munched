# Loading Animations

How-to guides for accessing the global loading animations API.

## Global Animation

For technical information read the [globalloading animation reference document](/references/global-loading-animation).

### Start Animation

```csharp
string Ticket = await JSRuntime.InvokeAsync<string>("StartLoading");
```

### Stop Animation

```csharp
JSRuntime.InvokeVoidAsync("StopLoading", Ticket);
```