# Sulu Website User Bundle (WIP)

## Features

 - Website User
 - Login
 - Registration
 - Profile
 - Double Opt-In

## Installation

### 1. Add Bundle to WebsiteKernel (app/WebsiteKernel.php):

```php
$bundles[] = new Symfony\Bundle\SecurityBundle\SecurityBundle();
$bundles[] = new L91\Sulu\Bundle\WebsiteUserBundle\L91SuluWebsiteUserBundle();
 ```

### 2. Add new routes to website routing config (app/config/website/routing.yml)

```yml
l91_sulu_website_user:
    type: portal
    resource: "@L91SuluWebsiteUserBundle/Resources/config/routing.yml"
```

### 3. Add Security system to your webspace

```xml
    <security>
        <system>Website</system>
    </security>
```

### 4. Update your website security file (app/config/website/security.yml)

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
        - { path: ^/[^/]+/profile, roles: ROLE_USER }
        - { path: ^/[^/]+/(.*), roles: IS_AUTHENTICATED_ANONYMOUSLY }

    firewalls:
        website:
            pattern: ^/
            anonymous: ~
            form_login:
                login_path: l91_sulu_website_user.login
                check_path: l91_sulu_website_user.login_check
            logout:
                path: l91_sulu_website_user.logout
                target: l91_sulu_website_user.login

sulu_security:
    checker:
        enabled: true
```

### 5. Create your Templates

Create the following templates in your theme under `<your_theme>/templates/security`

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

```yml
liip_theme:
    path_patterns:
        app_resource:
            - %%app_path%%/../../src/Client/Bundle/WebsiteBundle/Resources/themes/%%current_theme%%/%%template%%
```

### 6. Set Config

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
