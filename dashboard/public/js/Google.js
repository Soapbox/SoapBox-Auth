var Google = {
    user: {},
    init: () => {
        var params = new URL(document.location).searchParams;
        var timeDiff = new Date().getTime() - params.get("state");

        if (params.get("code") && params.get("state") && timeDiff < 3600000) {
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
        $("#google-login-verb").html("Login");
        $("#login-with-google").unbind("click");
    },
    clearURL: () => {
        window.history.pushState({}, document.title, "/");
    },
    complete: () => {
        Google.clearURL();
        $("#collapseTwo").collapse("show");
        Login.provider = "google";
        Login.access_token = Google.user.code;
    }
};

$(document).ready(Google.init);
