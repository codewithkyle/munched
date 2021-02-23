using System;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Components;
using Microsoft.JSInterop;
using Client.Models.API;

namespace Client.Pages.Utility
{
    public class MaintenanceBase : ComponentBase
    {

        [Inject]
        private NavigationManager NavigationManager { get; set; }

        [Inject]
        private IJSRuntime JSRuntime { get; set; }

        protected override async Task OnInitializedAsync()
        {
            ResponseCore Response = await JSRuntime.InvokeAsync<ResponseCore>("MaintenanceCheck");
			if (Response.Success){
				NavigationManager.NavigateTo("/");
			} else {
				switch (Response.StatusCode){
					case 503:
						NavigationManager.NavigateTo("/maintenance");
						break;
					default:
						NavigationManager.NavigateTo("/");
						break;
				}
			}
        }
    }
}
