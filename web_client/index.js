const express = require("express");

// Import the axios library, to make HTTP requests
const axios = require("axios");
const cors = require("cors");

// This is the client ID and client secret that you obtained
// while registering the application
const clientID = "c7aae19cb2cf0733b019";
const clientSecret = "cb3094d64447c69f5e8931cf3cd91b0053dbdce2";

const app = express();

// Declare the redirect route
app.get("/oauth/redirect", (req, res) => {
  // The req.query object has the query params that
  // were sent to this route. We want the `code` param
  const requestToken = req.query.code;
  axios({
    // make a POST request
    method: "post",
    // to the Github authentication API, with the client ID, client secret
    // and request token
    url: `https://github.com/login/oauth/access_token?client_id=${clientID}&client_secret=${clientSecret}&code=${requestToken}`,
    // Set the content type header, so that we get the response in JSOn
    headers: {
      accept: "application/json"
    }
  }).then(response => {
    // Once we get the response, extract the access token from
    // the response body
    const accessToken = response.data.access_token;
    // redirect the user to the welcome page, along with the access token
    res.redirect(`/welcome.html?access_token=${accessToken}`);
  });
});

app.use(express.static(__dirname + "/public"));
app.use(cors());
app.listen(8080);
