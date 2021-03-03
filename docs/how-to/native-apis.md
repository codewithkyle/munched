# Native APIs

## Prompt

```csharp
string Reponse = await JSRuntime.InvokeAsync<string>("Prompt", "Prompt label text.", "Optional placeholder value");
````

## Confirm

### Blazor

```csharp
bool Reponse = await JSRuntime.InvokeAsync<bool>("Confirm", "Confirm label text.");
````

## Focus Element

```csharp
JSRuntime.InvokeVoidAsync("FocusElement", ".query-selector-string");
```

## Document Title Override

```csharp
JSRuntime.InvokeVoidAsync("SetTitle", "Custom document title");
```