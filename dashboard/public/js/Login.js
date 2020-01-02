var Login = {
    provider: "",
    access_token: "",
    slug: "",
    init: () => {
        $("#login").click(Login.complete);
    },
    complete: () => {
        Login.slug = $("#slug").val();
        console.log(Login.provider, Login.access_token, Login.slug);
    }
};

$(document).ready(Login.init);
