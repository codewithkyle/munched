namespace Client.Models.Data
{
    public class User
    {
        public string Name {get;set;}
        public string Email {get;set;}
        public string Uid {get;set;}
        public string[] Groups {get;set;}
        public int Suspended {get;set;}
        public int Verified {get;set;}
        public int Admin {get;set;}
    }
}