# Upload directory hardening (defense in depth — finding 4.1)

`.htaccess` here disables script execution for uploaded content.

The same file is committed into every web-served upload directory:

    public/191014            public/formsubmissions   public/pitch_decks
    public/documents         public/organizations     public/orgnizations
    public/icons/flags       public/qrcodes           public/programs/teams
    public/images/logo       public/images/resource   public/images/slider
    public/images/blog       storage/app/public   (served through public/storage)

Several of those directories are gitignored, so their `.htaccess` was added with
`git add -f`. If a deploy recreates one of them empty (or `storage/app/public` is
a separate mount), put the file back:

    sudo cp .deploy/upload-hardening/.htaccess storage/app/public/.htaccess
    sudo chown www-data:www-data storage/app/public/.htaccess

`public/.htaccess` also carries one rewrite rule that returns 403 for script
extensions under all of these prefixes, so a missing per-directory file does not
reopen the hole.

After a deploy, open one existing image from each directory. A 500 on every file
in a directory means the vhost's `AllowOverride` does not permit the directives in
this file; fix the vhost (or use the server-level rule below) rather than removing
the guard.

## Nginx, or Apache with `AllowOverride None`

`.htaccess` files are inert there. Add to the server block instead:

    location ~* ^/(storage|191014|organizations|orgnizations|formsubmissions|pitch_decks|documents|qrcodes|icons|images|programs|hackathon|csv|uploads)/.*\.(php|php[3-8]|phtml|pht|phps|phar|cgi|pl|py|rb|sh|asp|aspx|jsp)$ {
        deny all;
    }

Application-level validation (FileManagerService) is the primary control; this
is the backstop.
