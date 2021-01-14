using System;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Components;
using Microsoft.JSInterop;
using Munched.Models;

namespace Munched.Pages
{
    public class RegisterBase : ComponentBase
    {

        [Inject]
        private NavigationManager NavigationManager { get; set; }

        [Inject]
        private IJSRuntime JSRuntime { get; set; }

        public RegistrationForm RegistrationForm = new RegistrationForm();

        public async Task RegisterUser()
        {
            bool Success = await JSRuntime.InvokeAsync<bool>("RegisterUser", RegistrationForm.Email, RegistrationForm.Password, RegistrationForm.FullName);
            Console.WriteLine(Success);
        }
    }
}