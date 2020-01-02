var Google = {
    Auth: {},
    SCOPE: "https://www.googleapis.com/auth/drive.metadata.readonly",
    user: {},
    apiKey: "AIzaSyBa2dl5sEP6dCxY9XmyJUFjmAQrOyBdJAs",
    clientId:
        "980333083597-kal3pf88u8jr8v4pn09l5bv91vfsb6bl.apps.googleusercontent.com",
    init: () => {
        // Load the API's client and auth2 modules.
        // Call the initClient function after the modules load.
        gapi.load("client:auth2", Google.initClient);
        $("#google-login-verb").prepend(
            "<span class='fa fa-spinner fa-spin'></span> "
        );
    },
    initClient: () => {
        // Retrieve the discovery document for version 3 of Google Drive API.
        // In practice, your app can retrieve one or more discovery documents.
        var discoveryUrl =
            "https://www.googleapis.com/discovery/v1/apis/drive/v3/rest";

        // Initialize the gapi.client object, which app uses to make API requests.
        // Get API key and client ID from API Console.
        // 'scope' field specifies space-delimited list of access scopes.
        gapi.client
            .init({
                apiKey: Google.apiKey,
                discoveryDocs: [discoveryUrl],
                clientId: Google.clientId,
                scope: Google.SCOPE
            })
            .then(function() {
                Google.Auth = gapi.auth2.getAuthInstance();

                // Listen for sign-in state changes.
                Google.Auth.isSignedIn.listen(Google.updateSigninStatus);

                // Handle initial sign-in state. (Determine if user is already signed in.)
                Google.setSigninStatus();

                // Call handleAuthClick function when user clicks on
                //      "Sign In/Authorize" button.
                $("#login-with-google").click(function() {
                    Google.handleAuthClick();
                });
                // $('#revoke-access-button').click(function() {
                //   revokeAccess();
                // });
            });
    },
    handleAuthClick: () => {
        if (Google.Auth.isSignedIn.get()) {
            // User is authorized so move on.
            Login.provider = "google";
            $("#collapseTwo").collapse("show");
            Google.complete();
            return;
        } else {
            // User is not signed in. Start Google auth flow.
            Google.Auth.signIn();
        }
    },
    setSigninStatus: isSignedIn => {
        Google.user = Google.Auth.currentUser.get();
        var isAuthorized = Google.user.hasGrantedScopes(Google.SCOPE);
        if (isAuthorized) {
            $("#google-login-verb").html("Continue");
        } else {
            $("#google-login-verb").html("Login");
        }
    },
    updateSigninStatus: isSignedIn => {
        Google.setSigninStatus();
    },
    complete: () => {
        var response = Google.user.getAuthResponse();
        Login.provider = "google";
        Login.access_token = response.access_token;
        // Login.complete();
    }
};
