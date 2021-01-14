using System.ComponentModel.DataAnnotations;

namespace Munched.Models
{
    public class LoginForm
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