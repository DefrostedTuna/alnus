Alnus
---
Alnus is a library to add roles and permissions to a Laravel project and is built to work alongside Laravel's Gate system. Alnus hill is a reference to something called, wait for it... [Gate](https://en.wikipedia.org/wiki/Gate_(novel_series)).

### Quick Installation

Composer is the best way to install Alnus.

```
composer require defrostedtuna/alnus
```

Otherwise you could just place it in your `composer.json` file.

```javascript
"require": {
	"defrostedtuna/alnus": "^1.0"
},
 ```
 
 ### Service Provider
 After installing, you must place the service provider into `config/app.php`
 
 ```php
 'providers' => [

    // Lots of providers here
    
 	DefrostedTuna\Alnus\AlnusServiceProvider::class,
    
    // Some other jargon afterwards
    
 ],
 ```
 
 ### Migrations
 Migrations have been made to incorporate this into an application out of the box. 
 Simply run `php artisan migrate` to have the migration tables created.
 
 **Note:** By default, Alnus is set to migrate a **uuid** field as a foreign key in reference to the *users* table. 
 
Personally, I use the package `webpatser/laravel-uuid` for this matter. [Here is a very good article on implementing uuids into a Laravel project](https://medium.com/@steveazz/setting-up-uuids-in-laravel-5-552412db2088#.ytme0xw00).

### Trait
To finish setup, attach the `RolesAndPermissions` trait onto your user model.

```php
<?php

namespace App\Models\User;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use DefrostedTuna\Alnus\Traits\RolesAndPermissions;

class User extends Authenticatable
{
    use Notifiable;
    use RolesAndPermissions;

```
**Note:** Alnus is configured to look for the user model under the App\Models directory. If you would like to change this, publish the config file and point `config/alnus.php` to a location of your choosing.

```
php artisan vendor:publish --provider="DefrostedTuna\Alnus\AlnusServiceProvider"
```

---

### Usage

Once the `RolesAndPermissions` trait has been placed onto the user model, you will have access to a variety of functions.

Some examples:

```php
// String, Role object, or an array of either.
$role = "administrator";

$user->assignRole($role);
$user->revokeRole($role);
$user->revokeAllRoles();
$user->syncRoles($role); // Also accepts ids as well.
```

What if you want to check for roles and permissions?

```php
// String, Role/Permission object, or an array of either.
$role = "moderator";
$permission = "upate_post";

$user->hasRole($role);
$user->isA($role); // $user->isA('moderator');
$user->isAn($role); //$user->isAn('administrator');
$user->isAbleTo($permission);
```

### Creating Role and Permissions

Roles and permissions are actually quite simple. They consist of a name, and a label. To create one, simply assign both of these attributes to the record.

```php
$role = new DefrostedTuna\Alnus\Models\Role();

$role->name = 'administrator';
$role->label = 'the site administrator';

$role->save();

$permission = new DefrostedTuna\Alnus\Models\Permission();

$permission->name = 'update_post';
$permission->label = 'Permission to update post';

$permission->save();
```

Finally, attach the permission to the role.

```php
$role->givePermission($permission);
```

---

### Integration with Laravel's GATE system

I mentioned that this is designed to be easily used with Laravel's gate system,  lets see an example of how I typically do this.

Assuming we have three users, the first user has the role of 'administrator' and has full control of the site. The second is a 'moderator' who has permission to update all posts. Finally, our third user will have no permissions and can only update thier own post.

I prefer to use policies when working with a project. I'll use policies in this example, however you are free to define these however you wish.

```php
// PostPolicy.php

public function before($user, $ability)
{
	if ($user->isAn('administrator') {
    	return true;
    }
}

public function update(User $user, Post $post)
{
	return ($user->isAbleTo('update_post') || $user->owns($post)) true : false;
}
```

```php
// routes/web.php

Route::get('posts/{post}/edit, [
	'uses' => 'PostController@edit',
    'middleware' => 'can:update,post'
]);
```

As you can see in this example, we are able to check three different levels of access. First, the administrator is given access prior to running the check. The moderator who has the ability to update posts is given access. Our third user would only be given access if they are the owner of the post, locking them out from changing a post belonging to another user.

That's pretty much it. This is fairly straightforward and most of the gate checks will mimic the permission names, allowing for super easy use. Bonus is that you'll be able to perform additional logic for the special cases, such as checking to see if a user owns a post without needing to muck up your code too much.