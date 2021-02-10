namespace Client.Models.Data
{
    public class User
    {
        public string name {get;set;}
        public string email {get;set;}
        public string uid {get;set;}
        public string[] groups {get;set;}
        public int id {get;set;}
        public int suspended {get;set;}
        public int verified {get;set;}
        public int admin {get;set;}
    }
}