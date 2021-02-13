using System;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Components;
using Microsoft.JSInterop;
using Client.Models.API;
using Client.Models.Globals;

namespace Client.Models.Pages
{
    public class UserPage : ComponentBase
    {

        [Inject]
        public NavigationManager NavigationManager { get; set; }

        [Inject]
        public IJSRuntime JSRuntime { get; set; }

        protected override async Task OnInitializedAsync()
        {
            ResponseCore TokenCheckResponse = await JSRuntime.InvokeAsync<ResponseCore>("RefreshToken");
            if (!TokenCheckResponse.Success)
            {
                NavigationManager.NavigateTo("/");
                return;
            }
            ProfileResponse Profile = await JSRuntime.InvokeAsync<ProfileResponse>("GetProfile");
            if (Profile.Success){
                CurrentUser.SetCurrentUser(Profile.User);
            }
            await Main();
        }

        protected virtual async Task Main() {}
    }
}