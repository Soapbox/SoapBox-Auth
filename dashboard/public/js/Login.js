var Login = {
    provider: "",
    access_token: "",
    slug: "",
    KEY: "login",
    init: () => {
        $("#login").click(Login.complete);
    },
    complete: () => {
        Login.slug = $("#slug").val();
        console.log(Login.provider, Login.access_token, Login.slug);

        $("#login-in").show();

        var url = `https://api.services.soapboxdev.com/auth/login`;

        $.post(
            url,
            {
                oauth_code: Login.access_token,
                "soapbox-slug": Login.slug,
                provider: Login.provider,
                redirectUri: "https://dashboard.services.soapboxdev.com"
            },
            null,
            "json"
        )
            .done(response => {
                console.log(response);
                localStorage.setItem(Login.KEY, response.token);
                location.href = "/app";
            })
            .fail(error => {
                $("#error-message-text").text(error.responseText);
                $("#error-message").show();
                setTimeout(() => {
                    $("#error-message").hide();
                }, 3000);
            })
            .always(() => {
                $("#login-in").hide();
            });
    }
};

$(document).ready(Login.init);
