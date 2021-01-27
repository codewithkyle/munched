using System;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Components;
using Microsoft.JSInterop;
using Munched.Models.Forms;
using Munched.Models.API;

namespace Munched.Pages
{
    public class DashboardBase : ComponentBase
    {

        [Inject]
        private NavigationManager NavigationManager { get; set; }

        [Inject]
        private IJSRuntime JSRuntime { get; set; }

        public bool ViewIsReady = false;

        public string OldPassword = "";
        public string NewPassword = "";

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

        public async Task UpdateProfile()
        {
            ResponseCore Response = await JSRuntime.InvokeAsync<ResponseCore>("UpdatePassword", OldPassword, NewPassword);
        }
    }
}