# Puddle
### A stupidly simple headless blogging system written in PHP

I expect I will be the only person who ever uses this, but if you happen to stumble across it, I'll explain what it is and how to use it.

Essentially, it's a really simple blogging system which lets you just embed the content of into your existing website, so you don't have to install something else like Wordpress, or have a blog on a separate part of your site which might not match the theme. You just call a few methods and your blog posts or lists are displayed within your current website. There is no database required, it all runs on files. No editor either, posts are created and deleted via the CLI.

There are other headless blogging systems out there, but I decided to make my own because they all looked too complex for my needs.

It doesn't come with any css styling, but everything has class identifiers, so you can style the output however you want to fit in with your website.

#### Requirements
- PHP 8.3+
- Server setup to pass 4 named query string parameters to your blog page for nice urls (See: URL configuration).

#### Pages
There are 2 types of pages in Puddle (sort of). 

`PostPage` which displays a single post. And `PostList` which displays any given list of posts, such as searching by Tag or viewing Recent Posts (those are separate page types technically, but they are routed through `PostList`).


#### How to use
1. Clone down this repo into your own web project (it does not need to be web accessible)
2. Run `composer install` within the `puddle` directory to install the required packages
3. Copy the `blog.json.dist` file to outside of your `puddle` directory and rename it `blog.json`. So your directories should look like:
    - /myapp
      - puddle/
      - blog.json
      - all_my_other_stuff.files
4. Set the config values in that `blog.json` file:
- `content_path` - This is the path to the directory where you want the blog files to be created.
- `metadata_file` - This is the full path to the file where you want the post metadata to be stored.
- `tags` - This is an array of all the possible tags you want to be able to use on your posts.
- `url` - This is the URL to the blog page on your site, where the content will be rendered.
- `posts_per_page` - This is how many posts you want displayed per page when viewing a list of posts.
5. Now you can create some posts, using the `puddle` script. Simply run `php puddle add` and follow the prompts to add a title, tags, and post image url. This will add the post to the metadata file, and also create a blank mark-down file in your `content_path` for you to add your post content to.
6. Add your content to the `<id>.md` file which was created. This is a mark-down file so you can use any normal mark-down formatting.
7. Render the blog content on your site. In whichever PHP script you have to render your blog page, you just need to call the Page method to work out which page we want to see and then render() it.

Here is an example. If you had a really basic webpage which was requiring a header and a footer and then echoing some content between them, it might look like:

```php
# Including my site header here

require_once __DIR__ . '/../blog/vendor/autoload.php';
$Config = Config::load(file: __DIR__ . '/../blog.json');

try {
    $Page = Page::which(config: $Config);
    $Page->render();
} catch (Exception $e) {
    echo '<div class="text-center">Unable to load page. 😢</div>';
}

# Including my site footer here

```

If you're using an MVC system instead of a basic procedural system, you'll want to put that code wherever you are rendering the blog page. I'm sure you can work it out. And obviously, change whatever you want to in that. If you want to return the content instead of echoing it, use `getDisplay()` instead of `render()`.

The `Page::which()` method will determine which page we want to view, e.g. "Blog post 123", or "Most recent posts", or "All posts tagged with `my-tag`", etc...

This is done based on the query string parameters found in the `$_GET` array. 


#### URL Configuration

In order for the system to work out which page we want, and to have nice urls, such as `/blog/1/2025/02/14/my_blog_post`, we need to configure our server to handle it.

How this is done depends on the server software you are using. For example if I was using Apache, I might do this in an `.htaccess` file. Or if I was using nginx, I might do it as a nginx rule.

However, I am using Caddy, so my example will be based on that. But it'll be fairly similiar for other server software.

The params we need to expect on our blog page are: `p1`, `p2`, `p3` and `p4`. So, without nice urls that would look like: `https://mywebsite.com/blog/?p1=something&p2=something&p3=something&p4=something`

In Caddy, I do this like so in the Caddyfile:

```caddy
    @blogPath path_regexp blog ^\/blog\/?([^\/]*)\/?([^\/]*)\/?([^\/]*)\/?([^\/]*)\/?(.*)?$
    rewrite @blogPath /blog.php?p1={re.blog.1}&p2={re.blog.2}&p3={re.blog.3}&p4={re.blog.4}
```

So, apply that rewrite regex to your server, however it's done for your particular server software.

Assuming it works, Puddle should now be able to work out what page you want:

(Assuming our blog is located at: `/blog`), then it would be:

- `/blog` - Most Recent Posts
- `/blog/page/2` - Page 2 of Most Recent Posts
- `/blog/tag/abc` - List of posts tagged with `abc`
- `/blog/tag/abc/page/2` - Page 2 of posts tagged with `abc`
- `/blog/1/2025/04/05/my_post` - Blog post with id `1` (The rest is ignored and just to make a nice url)


#### CLI Commands
- `php puddle add` - Add a new post
- `php puddle delete <id>` - Delete post with given `<id>`



