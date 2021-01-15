using System;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Components;
using Microsoft.JSInterop;
using Munched.Models.Forms;
using Munched.Models.API;

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
            RegistrationForm.Submit();
            StateHasChanged();
            FormResponse Response = await JSRuntime.InvokeAsync<FormResponse>("RegisterUser", RegistrationForm.Email, RegistrationForm.Password, RegistrationForm.Name);
            if (Response.Success){
                RegistrationForm.Succeed();
            } else {
                RegistrationForm.Fail(Response.FieldErrors[0]);
            }
            StateHasChanged();
        }
    }
}