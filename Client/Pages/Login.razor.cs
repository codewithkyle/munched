using System;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Components;
using Microsoft.JSInterop;
using Munched.Models.Forms;
using Munched.Models.API;

namespace Munched.Pages
{
    public class LoginBase : ComponentBase
    {

        [Inject]
        private NavigationManager NavigationManager { get; set; }

        [Inject]
        private IJSRuntime JSRuntime { get; set; }

        public LoginForm LoginForm = new LoginForm();

        public bool ViewIsReady = false;

        public bool TrustDevice = true;

        protected override async Task OnInitializedAsync()
        {
            ResponseCore TokenCheckResponse = await JSRuntime.InvokeAsync<ResponseCore>("RefreshToken");
            if (TokenCheckResponse.Success){
                NavigationManager.NavigateTo("/dashboard");
            } else {
                ViewIsReady = true;
            }
        }

        public async Task LoginUser()
        {
            LoginForm.Submit();
            StateHasChanged();
            LoginResponse Response = await JSRuntime.InvokeAsync<LoginResponse>("LoginUser", LoginForm.Email, LoginForm.Password, TrustDevice);
            if (Response.Success){
                LoginForm.Succeed();
                if (Response.IsPendingEmailVerificaiton){
                    NavigationManager.NavigateTo("/verification/pending");
                } else {
                    NavigationManager.NavigateTo("/dashboard");
                }
            } else {
                if (Response.FieldErrors != null){
                    LoginForm.Fail(Response.FieldErrors[0]);
                } else {
                    LoginForm.Fail(Response.Error);
                }
            }
            StateHasChanged();
        }
    }
}