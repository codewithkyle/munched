using Client.Models.Data;

namespace Client.Models.Globals
{
    public static class CurrentUser
    {
        public static string Name = null;
        public static string Email = null;
        public static string Uid = null;
        public static string[] Groups = null;
        public static int Suspended = 1;
        public static int Verified = 0;
        public static int Admin = 0;

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