using System;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Components;
using Microsoft.JSInterop;
using Client.Models.API;

namespace Client.Models.Pages
{
    public class UserPage : ComponentBase
    {

        [Inject]
        private NavigationManager NavigationManager { get; set; }

        [Inject]
        private IJSRuntime JSRuntime { get; set; }

        public bool ViewIsReady = false;

        protected override async Task OnInitializedAsync()
        {
            ResponseCore TokenCheckResponse = await JSRuntime.InvokeAsync<ResponseCore>("RefreshToken");
            if (!TokenCheckResponse.Success)
            {
                NavigationManager.NavigateTo("/");
            }
            else
            {
                ViewIsReady = true;
            }
        }
    }
}