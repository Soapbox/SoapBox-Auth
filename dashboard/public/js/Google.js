var Google = {
    user: {},
    init: () => {
        var params = new URL(document.location).searchParams;

        if (
            params.get("code") &&
            params.get("state") &&
            new Date().getTime() - params.get("state") < 60000
        ) {
            Google.continueAuth(params);
        } else {
            Google.createAuthURL();
        }
    },
    continueAuth: params => {
        Google.user.code = params.get("code");
        $("#google-login-verb").html("Continue");
        $("#login-with-google").click(e => {
            e.preventDefault();
            Google.complete();
        });
        Google.complete();
    },
    createAuthURL: () => {
        var href = `/google-login`;
        $("#login-with-google").attr("href", href);
    },
    complete: () => {
        $("#collapseTwo").collapse("show");
        Login.provider = "google";
        Login.access_token = Google.user.code;
    }
};

$(document).ready(Google.init);
