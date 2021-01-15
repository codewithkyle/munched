using System;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Components;
using Microsoft.JSInterop;
using Munched.Models.API;

namespace Munched.Pages.Verification
{
    public class PendingEmailVerificationBase : ComponentBase
    {

        [Inject]
        private NavigationManager NavigationManager { get; set; }

        [Inject]
        private IJSRuntime JSRuntime { get; set; }

        public async Task ResendVerificationEmail()
        {
            ResponseCore Response = await JSRuntime.InvokeAsync<ResponseCore>("ResendVerificationEmail");
            Console.WriteLine(Response.Success);
        }
    }
}