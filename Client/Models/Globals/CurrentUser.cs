using Client.Models.Data;

namespace Client.Models.Globals
{
    public static class CurrentUser
    {
        public static string Name = null;
        public static string Email = null;
        public static string Uid = null;
        public static string[] Groups = null;
        public static bool Suspended = false;
        public static bool Verified = true;
        public static bool Admin = false;

        public static void SetCurrentUser(User User)
        {
            Name = User.Name;
            Email = User.Email;
            Uid = User.Uid;
            Groups = User.Groups;
            Suspended = User.Suspended;
            Verified = User.Verified;
            Admin = User.Admin;
        }
    }
}