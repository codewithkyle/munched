using Microsoft.AspNetCore.Components;
using Microsoft.JSInterop;
using Client.Models.Globals;
using Client.Models.Forms;
using System.Threading.Tasks;
using Client.Models.API;
using Microsoft;

namespace Client.Shared.Modals
{
    public class EditProfileModalBase : ComponentBase
    {
        [Inject] public IJSRuntime JSRuntime { get; set; }
		public EditProfileForm ProfileForm = new EditProfileForm();
		public PasswordForm PasswordForm = new PasswordForm();
		public bool ChangingPassword = false;
		[Parameter] public EventCallback CloseModal { get; set; }

		protected override void OnInitialized()
		{
			ProfileForm.Name = CurrentUser.Name;
			ProfileForm.Email = CurrentUser.Email;
			base.OnInitialized();
		}

		protected override void OnAfterRender(bool firstRender)
		{
			if (firstRender){
				JSRuntime.InvokeVoidAsync("FocusElement", "#modal-close-button");
			}
			base.OnAfterRender(firstRender);
		}

		public async Task UpdateProfile()
        {
            ProfileForm.Submit();
            StateHasChanged();
            FormResponse ProfileUpdateResponse = await JSRuntime.InvokeAsync<FormResponse>("UpdateProfile", ProfileForm.Name, ProfileForm.Email);
            if (ProfileUpdateResponse.Success){
                ProfileForm.Succeed();
				await JSRuntime.InvokeVoidAsync("Alert", "success", "Profile Updated", "Your profile has been updated successfully.");
            } else {
                if (ProfileUpdateResponse.FieldErrors != null){
                    ProfileForm.Fail(ProfileUpdateResponse.FieldErrors[0]);
                } else {
                    ProfileForm.Fail(ProfileUpdateResponse.Error);
                }
            }
            StateHasChanged();
        }

		public void OpenPasswordUpdateModal()
		{
			ChangingPassword = true;
			StateHasChanged();
		}

		public async Task UpdatePassword()
        {
            PasswordForm.Submit();
            StateHasChanged();
            FormResponse Response = await JSRuntime.InvokeAsync<FormResponse>("UpdatePassword", PasswordForm.OldPassword, PasswordForm.NewPassword);
            if (Response.Success){
                PasswordForm.Succeed();
				await JSRuntime.InvokeVoidAsync("Alert", "success", "Password Changed", "Your password has been updated successfully.");
				PasswordForm.OldPassword = null;
				PasswordForm.NewPassword = null;
				ChangingPassword = false;
            } else {
                if (Response.FieldErrors != null){
                    PasswordForm.Fail(Response.FieldErrors[0]);
                } else {
                    PasswordForm.Fail(Response.Error);
                }
            }
            StateHasChanged();
        }
    }
}
