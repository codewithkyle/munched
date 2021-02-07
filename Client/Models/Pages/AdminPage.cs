using System;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Components;
using Microsoft.JSInterop;
using Munched.Models.API;

namespace Munched.Models.Pages
{
    public class AdminPage : ComponentBase
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
                ResponseCore AdminVerificationResponse = await JSRuntime.InvokeAsync<ResponseCore>("VerifyAdmin");
                if (!AdminVerificationResponse.Success)
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
}