using System;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Components;
using Microsoft.JSInterop;
using Munched.Models;

namespace Munched.Pages
{
    public class LoginBase : ComponentBase
    {

        [Inject]
        private NavigationManager NavigationManager { get; set; }

        [Inject]
        private IJSRuntime JSRuntime { get; set; }

        public LoginForm LoginForm = new LoginForm();

        public async Task LoginUser()
        {
            bool Success = await JSRuntime.InvokeAsync<bool>("LoginUser", LoginForm.Email, LoginForm.Password);
            Console.WriteLine(Success);
        }
    }
}