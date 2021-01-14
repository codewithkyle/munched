using System;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Components;
using Microsoft.JSInterop;
using Munched.Models;

namespace Munched.Pages
{
    public class IndexBase : ComponentBase
    {

        [Inject]
        private NavigationManager NavigationManager { get; set; }

        [Inject]
        private IJSRuntime JSRuntime { get; set; }

        public RegistrationForm RegistrationForm = new RegistrationForm();
        public LoginForm LoginForm = new LoginForm();

        public async Task RegisterUser()
        {
            bool Success = await JSRuntime.InvokeAsync<bool>("RegisterUser", RegistrationForm.Email, RegistrationForm.Password);
            Console.WriteLine(Success);
        }

        public async Task LoginUser()
        {
            bool Success = await JSRuntime.InvokeAsync<bool>("LoginUser", LoginForm.Email, LoginForm.Password);
            Console.WriteLine(Success);
        }
    }
}