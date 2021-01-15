using System.ComponentModel.DataAnnotations;

namespace Munched.Models.Forms
{
    public class LoginForm : FormCore
    {
        [Required(
            ErrorMessage = "Your email address is required."
        )]
        public string Email { get; set; }

        [Required(
            ErrorMessage = "Your password is requried."
        )]
        public string Password { get; set; }
    }
}