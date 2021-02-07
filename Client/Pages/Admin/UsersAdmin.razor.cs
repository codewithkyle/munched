using System;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Components;
using Microsoft.JSInterop;
using Munched.Models.Forms;
using Munched.Models.API;

namespace Munched.Pages.Admin
{
    public class UsersAdmin : ComponentBase
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