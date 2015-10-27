# Loop

All our user feedback in one place. Built on [Spark](https://github.com/sparkphp/Spark).

Loop grabs feedback items from a variety of different sources, including Twitter, Satismeter, Zendesk, and the Manager Tool. Feedback is posted to HipChat and saved in a MySQL database for any later analysis.

## Setting Up

For local development, [Homestead](http://laravel.com/docs/4.2/homestead) is recommended. Edit the folders and sites entries in Homestead.yaml to include the following:

```yaml
folders:
    - map: local/path/to/loop
      to: /home/vagrant/loop

sites:
    - map: loop.dev
      to: /home/vagrant/loop/web
```

For the various endpoints to work, you will need a .env file in Loop's root directory with any keys you need. Take a look at Configuration.php to see what fields are expected.

## Testing

There is no official testing procedure yet, so there are a few important things to keep in mind when testing:

- Using `?debug=true` on an endpoint URL will still show new feedback items in the response body, but will prevent the endpoint from actually doing anything with those feedback items. Use this to test if an endpoint is getting the correct values but you don't want to post anything to HipChat or save anything in the database.
- Any new content will be automatically sent to the Loop room in HipChat, where the entire company can see it. Consider setting a different HipChat room key in your local .env file.
