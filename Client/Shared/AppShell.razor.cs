using System;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Components;
using Microsoft.JSInterop;
using Client.Models.API;
using Client.Models.Globals;

namespace Client.Shared
{
    public class AppShellBase : LayoutComponentBase
    {
        [Inject]
        public IJSRuntime JSRuntime { get; set; }

        protected override async Task OnInitializedAsync()
        {
            ProfileResponse Profile = await JSRuntime.InvokeAsync<ProfileResponse>("GetProfile");
            if (Profile.Success){
                CurrentUser.SetCurrentUser(Profile.User);
            }
        }
    }
}
