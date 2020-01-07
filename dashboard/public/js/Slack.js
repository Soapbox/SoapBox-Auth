var Slack = {
    CLIENT_ID: "2287959210.829652005893",
    SCOPE: "identity.basic,identity.email",
    REDIRECT_URL: "http://dashboard.services.soapboxdev.com/",
    user: {},
    init: () => {
        var params = new URL(document.location).searchParams;

        if (
            params.get("code") &&
            params.get("state") &&
            new Date().getTime() - params.get("state") < 600000
        ) {
            $("#slack-login-verb").prepend(
                "<span class='fa fa-spinner fa-spin'></span> "
            );
            Slack.triggerOAUTH(params.get("code"));
        } else {
            Slack.createAuthURL();
        }
    },
    triggerOAUTH: code => {
        var url = `/slack-login`;

        $.get(url, {
            code: code
        }).done(response => {
            if (response.ok) {
                Slack.user = response;
                $("#slack-login-verb").html("Continue");
                $("#login-with-slack").click(e => {
                    e.preventDefault();
                    Slack.complete();
                });
            } else {
                $("#slack-login-verb").html("Login");
                Slack.createAuthURL();
            }
        });
    },
    createAuthURL: () => {
        var href = `https://slack.com/oauth/authorize?scope=${
            Slack.SCOPE
        }&client_id=${Slack.CLIENT_ID}&redirect_uri=${encodeURIComponent(
            Slack.REDIRECT_URL
        )}&state=${new Date().getTime()}`;
        $("#login-with-slack").attr("href", href);
    },
    complete: () => {
        $("#collapseTwo").collapse("show");
        Login.provider = "slack";
        Login.access_token = Slack.user.access_token;
    }
};

$(document).ready(Slack.init);
