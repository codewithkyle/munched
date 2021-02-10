using System;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Components;
using Microsoft.JSInterop;
using Client.Models.API;

namespace Client.Models.Pages
{
    public class AdminPage : ComponentBase
    {

        [Inject]
        public NavigationManager NavigationManager { get; set; }

        [Inject]
        public IJSRuntime JSRuntime { get; set; }

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
                ResponseCore AdminVerificationResponse = await JSRuntime.InvokeAsync<ResponseCore>("VerifyAdmin");
                if (!AdminVerificationResponse.Success)
                {
                    NavigationManager.NavigateTo("/");
                }
            }
            ViewIsReady = true;
            await Main();
        }

        protected virtual async Task Main() {}
    }
}