using System.ComponentModel.DataAnnotations;

namespace Munched.Models
{
    public class RegistrationForm
    {
        [Required(
            ErrorMessage = "An email is requried."
        )]
        public string Email { get; set; }

        [Required(
            ErrorMessage = "A password is requried."
        )]
        [StringLength(
            128,
            MinimumLength = 6,
            ErrorMessage = "Passwords must be at least 6 characters."
        )]
        public string Password { get; set; }
    }
}