using System;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Components;
using Microsoft.JSInterop;
using Client.Models.Forms;
using Client.Models.API;
using Client.Models.Pages;
using Client.Models.Data;
using System.Collections.Generic;

namespace Client.Pages.Admin
{
    public class UsersAdmin : AdminPage
    {

        public List<User> Users = new List<User>();
        public bool IsLoadingUserData = true;
        public int UsersPerPage = 10;
        public int Page = 0;

        protected override async Task Main()
        {
            await LoadUserData();
        }

        public async Task LoadUserData()
        {
            UsersResponse UsersResponse = await JSRuntime.InvokeAsync<UsersResponse>("GetUsers", Page, UsersPerPage);
            if (UsersResponse.Success)
            {
                Users = UsersResponse.Users;
                IsLoadingUserData = false;
            }
            else
            {
                await JSRuntime.InvokeVoidAsync("Alert", "error", "Something Went Wrong", UsersResponse.Error);
            }
            StateHasChanged();
        }
    }
}