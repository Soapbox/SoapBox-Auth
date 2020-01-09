var Google = {
    user: {},
    init: () => {
        var params = new URL(document.location).searchParams;

        console.log(params);

        if (
            params.get("code") &&
            params.get("state") &&
            new Date().getTime() - params.get("state") < 60000
        ) {
            Google.createContinueAuthUrl(params);
        } else {
            Google.createAuthURL();
        }
    },
    createContinueAuthUrl: params => {
        Google.user.code = params.get("code");
        $("#google-login-verb").html("Continue");
        $("#login-with-google").click(e => {
            e.preventDefault();
            Google.complete();
        });
    },
    createAuthURL: () => {
        var href = `/google-login`;
        $("#login-with-google").attr("href", href);
    },
    complete: () => {
        console.log(Google.user);
        $("#collapseTwo").collapse("show");
        Login.provider = "google";
        Login.access_token = Google.user.code;
    }
};

$(document).ready(Google.init);
