using System.ComponentModel.DataAnnotations;

namespace Munched.Models.Forms
{
    public class ForgotPasswordForm : FormCore
    {
        [Required(
            ErrorMessage = "Your email address is required."
        )]
        public string Email { get; set; }
    }
}