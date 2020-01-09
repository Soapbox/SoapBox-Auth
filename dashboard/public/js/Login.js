var Login = {
    provider: "",
    access_token: "",
    slug: "",
    KEY: "login",
    init: () => {
        $(document.login).submit(function(e) {
            Login.complete();
            return true;
        });
    },
    complete: () => {
        console.log(
            Login.provider,
            Login.access_token,
            document.login["soapbox-slug"].value
        );
        $("#login-in").show();
        document.login.oauth_code.value = Login.access_token;
    }
};

$(document).ready(Login.init);
