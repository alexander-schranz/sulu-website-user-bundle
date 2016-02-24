# Sulu Website User Bundle

## When use it?

You use Sulu to build an application, intranet, shop, ... where users can login, register and need to manage their 
contact data on the website. This bundle will give all basics you need:

 - Website Security User Role
 - Login Form
 - Registration Form
 - Password Forget / Reset Form
 - Profile Form
 - Double Opt-In, Single Opt-In or Admin activation
 - Notification Emails for every action (User + Admin)
 - Multiple WebSpace Support

## Installation

### 0. Install over composer

```bash
composer require l91/sulu-website-user-bundle:~1.0
composer install
```

### 1. Add Security Bundle to WebsiteKernel

`app/WebsiteKernel.php`:

```php
$bundles[] = new Symfony\Bundle\SecurityBundle\SecurityBundle();
```
 
### 2. Add Bundle to AbstractKernel:

`app/AbstractKernel.php`:

```php
new L91\Sulu\Bundle\WebsiteUserBundle\L91SuluWebsiteUserBundle(),
 ```

### 3. Add new routes to routing config

`app/config/website/routing.yml` and `app/config/admin/routing.yml`:

```yml
l91_sulu_website_user:
    type: portal
    resource: "@L91SuluWebsiteUserBundle/Resources/config/routing.yml"
```

### 4. Add Security system to your webspace

`app/Resources/webspaces/<your_webspace>.xml`:

```xml
    <security>
        <system>Website</system>
    </security>
```

### 5. Update your website security file

`app/config/website/security.yml`:

Example:

```yml
security:
    session_fixation_strategy: none

    access_decision_manager:
        strategy: affirmative

    acl:
        connection: default

    encoders:
        Sulu\Bundle\SecurityBundle\Entity\User:
            algorithm: sha512
            iterations: 5000
            encode_as_base64: false

    providers:
        sulu:
            id: sulu_security.user_repository

    access_control:
        - { path: /%locale%/profile, roles: ROLE_USER }

    firewalls:
        website:
            pattern: ^/
            anonymous: ~
            form_login:
                login_path: /%locale%/login
                check_path: /%locale%/login-check
            logout:
                path: /%locale%/logout
                target: /%locale%/login

sulu_security:
    checker:
        enabled: true
```

### 6. Create your Templates

Create the following templates in your theme under `<your_theme>/templates/security`.
(see https://github.com/alexander-schranz/sulu-website-user-bundle/tree/master/Resources/themes/default/templates/security as examples)

 - `login.html.twig`
 - `registration.html.twig`
 - `confirmation.html.twig`
 - `password-forget.html.twig`
 - `password-reset.html.twig`
 - `profile.html.twig`
 - `emails/registration-user.html.twig`
 - `emails/password-forget-user.html.twig`
 
**Update Liip Theme Bundle with your Bundle**

https://github.com/sulu-io/sulu/issues/1966

`app/config/config.yml`:

```yml
liip_theme:
    path_patterns:
        app_resource:
            - %%app_path%%/../../src/Client/Bundle/WebsiteBundle/Resources/themes/%%current_theme%%/%%template%%
```

### 7. Set Config

`app/config/config.yml`:

**Basic:**

```yml
l91_sulu_website_user:
    webspaces:
        <webspace_key>:
            from: no-reply@example.at
```

**Extended:**

```yml
l91_sulu_website_user:
    webspaces:
        <webspace_key>:
            from: no-reply@example.at
            to: ~
            reply_to: ~
            subject: ~
            role: Website
            form_types:
                contact: L91\Sulu\Bundle\WebsiteUserBundle\Form\Type\ContactType
                contact_address: L91\Sulu\Bundle\WebsiteUserBundle\Form\Type\ContactAddressType
                address: L91\Sulu\Bundle\WebsiteUserBundle\Form\Type\ContactAddressType
            login:
                from: ~
                to: ~
                reply_to: ~
                subject: ~
                templates:
                    form: '::templates/security/login.html.twig'
                    form_embed: '::templates/security/embed/login.html.twig'
                    admin: ~
                    user: ~
            registration:
                from: ~
                to: ~
                reply_to: ~
                subject: ~
                activate_user: false
                form_type: 'L91\Sulu\Bundle\WebsiteUserBundle\Form\Type\RegistrationType'
                templates:
                    form: '::templates/security/registration.html.twig'
                    admin: ~
                    user: '::templates/security/emails/registration-user.html.twig'
            confirmation:
                from: ~
                to: ~
                reply_to: ~
                subject: ~
                activate_user: true
                form_type: 'L91\Sulu\Bundle\WebsiteUserBundle\Form\Type\ConfirmationType'
                templates:
                    form: '::templates/security/confirmation.html.twig'
                    admin: ~
                    user: ~
            password_forget:
                from: ~
                to: ~
                reply_to: ~
                subject: ~
                form_type: 'L91\Sulu\Bundle\WebsiteUserBundle\Form\Type\PasswordForgetType'
                templates:
                    form: '::templates/security/password-forget.html.twig'
                    admin: ~
                    user: '::templates/security/emails/password-forget-user.html.twig'
            password_reset:
                from: ~
                to: ~
                reply_to: ~
                subject: ~
                form_type: 'L91\Sulu\Bundle\WebsiteUserBundle\Form\Type\PasswordResetType'
                templates:
                    form: '::templates/security/password-reset.html.twig'
                    admin: ~
                    user: ~
            profile:
                from: ~
                to: ~
                reply_to: ~
                subject: ~
                form_type: 'L91\Sulu\Bundle\WebsiteUserBundle\Form\Type\ProfileType'
                templates:
                    form: '::templates/security/profile.html.twig'
                    admin: ~
                    user: ~
```
