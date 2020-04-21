# Eloquent Filter


![alt text](./eloquent-filter.jpg "eloquent-filter")

[![Latest Stable Version](https://poser.pugx.org/mehdi-fathi/eloquent-filter/v/stable)](https://packagist.org/packages/mehdi-fathi/eloquent-filter)
![Run tests](https://github.com/mehdi-fathi/eloquent-filter/workflows/Run%20tests/badge.svg?branch=master)
[![License](https://poser.pugx.org/mehdi-fathi/eloquent-filter/license)](https://packagist.org/packages/mehdi-fathi/eloquent-filter)
[![GitHub stars](https://img.shields.io/github/stars/mehdi-fathi/eloquent-filter)](https://github.com/mehdi-fathi/eloquent-filter/stargazers)
[![StyleCI](https://github.styleci.io/repos/149638067/shield?branch=master)](https://github.styleci.io/repos/149638067)
[![Build Status](https://travis-ci.org/mehdi-fathi/eloquent-filter.svg?branch=master)](https://travis-ci.org/mehdi-fathi/eloquent-filter)

A package for filter data of models by query string.Easy to use and full dynamic.

The Eloquent Filter is stable on PHP 7.1,7.2,7.3,7.4 and Laravel 5.x,6.x,7.x.

## Basic Usage

Add Filterable trait to your models and set fields that you will want filter in whitelist.You can override this method in your models.

```php
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class User extends Model
{
    use Filterable;
    
    private static $whiteListFilter =[
        'id',
        'username',
        'email',
        'created_at',
        'updated_at',
    ];
}
```
You can set `*` char for filter in all fields as like below example:
 
```php
private static $whiteListFilter = ['*'];
```
You can add or set `$whiteListFilter` on the fly in your method.For example:

#### Set array to WhiteListFilter
Note that this method override `$whiteListFilter`
```php
User::setWhiteListFilter(['name']); 
```
#### Add new field to WhiteListFilter
```php
User::addWhiteListFilter('name'); 
```

### Use in your controller

Change your code on controller as like below example:

```php

namespace App\Http\Controllers;

use eloquentFilter\QueryFilter\ModelFilters\ModelFilters;

/**
 * Class UsersController.
 */
class UsersController
{
    /**
     * @param \eloquentFilter\QueryFilter\ModelFilters\ModelFilters $modelFilters
     */
    public function list(ModelFilters $modelFilters)
    {
          if (!empty($modelFilters->filters())) {
          
              $perpage = Request::input('perpage');
              Request::offsetUnset('perpage');
              $users = User::filter($modelFilters)->with('posts')->orderByDesc('id')->paginate($perpage,['*'],'page');
          } else {
              $users = User::with('posts')->orderByDesc('id')->paginate(10);
          }
    }
}
```

Note that you must unset your own param as perpage.Just you can set page param for paginate this param ignore from filter.

### Simple Example

You just pass data blade form to query string or generate query string in controller method.For example:

**Simple Where**
```
/users/list?filters[email]=mehdifathi.developer@gmail.com

SELECT ... WHERE ... email = 'mehdifathi.developer@gmail.com'
```

```
/users/list?filters[first_name]=mehdi&filters[last_name]=fathi

SELECT ... WHERE ... first_name = 'mehdi' AND last_name = 'fathi'
```

```
/users/list?filters[username][]=ali&filters[username][]=ali22&filters[family]=ahmadi

SELECT ... WHERE ... username = 'ali' OR username = 'ali22' AND family = 'ahmadi'
```
***Where by operator***

You can set any operator mysql in query string.

```
/users/list?filters[count_posts][op]=>&filters[count_posts][value]=35

SELECT ... WHERE ... count_posts > 35
```
```
/users/list?filters[username][op]=!=&filters[username][value]=ali

SELECT ... WHERE ... username != 'ali'
```
```
/users/list?filters[count_posts][op]=<&filters[count_posts][value]=25

SELECT ... WHERE ... count_posts < 25
```

****Special Params****

You can set special params `limit` and `orderBy` in query string for make query by that.
```
/users/list?filters[f_params][limit]=1

SELECT ... WHERE ... order by `id` desc limit 1 offset 0
```

```
/users/list?filters[f_params][orderBy][field]=id&filters[f_params][orderBy][type]=ASC

SELECT ... WHERE ... order by `id` ASC limit 10 offset 0
```
***Where between***

If you are going to make query whereBetween.You must fill keys `start` and `end` in query string.
you can set it on query string as you know.

```
/users/list?filters[created_at][start]=2016/05/01&filters[created_at][end]=2017/10/01

SELECT ... WHERE ... created_at BETWEEN '2016/05/01' AND '2017/10/01'
```

Also you can set jallali date in your params and eloquent-filter will detect jallali date and convert to gregorian then eloquent-filter generate new query. You just pass a jallali date by param

```
/users/list?filters[created_at][start]=1397/10/11 10:11:46&filters[created_at][end]=1397/11/17 10:11:46

SELECT ... WHERE ... created_at BETWEEN '2019-01-01 10:11:46' AND '2019-02-06 10:11:46'
``` 

****Advanced Where****
```
/users/list?filters[count_posts][op]=>&filters[count_posts][value]=10&filters[username][]=ali&filters[username][]=mehdi&filters[family]=ahmadi&filters[created_at][start]=2016/05/01&filters[created_at][end]=2020/10/01
&filters[f_params][orderBy][field]=id&filters[f_params][orderBy][type]=ASC

select * from `users` where `count_posts` > 10 and `username` in ('ali', 'mehdi') and 
`family` = ahmadi and `created_at` between '2016/05/01' and '2020/10/01' order by 'id' asc limit 10 offset 0
```

Just fields of query string be same rows table database in `$whiteListFilter` in your model or declare method in your model as override method.
Override method can be considered custom query filter.

### Custom query filter
If you are going to make yourself query filter you can do it easily.You just make a trait and use it on model:

```php
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait usersFilter.
 */
trait usersFilter
{
    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param                                       $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function username_like(Builder $builder, $value)
    {
        return $builder->where('username', 'like', '%'.$value.'%');
    }
}
```

Note that fields of query string be same methods of trait.Use trait in your model:

```
/users/list?filters[username_like]=a

select * from `users` where `username` like %a% order by `id` desc limit 10 offset 0
```

```php
class User extends Model
{
    use usersFilter,Filterable;

    protected $table = 'users';
    protected $guarded = [];
    private static $whiteListFilter =[
        'id',
        'username',
        'email',
        'created_at',
        'updated_at',
    ];
    
}
```
If you have any idea about the Eloquent Filter i will glad to hear that.You can make an issue or contact me by email. My email is mehdifathi.developer@gmail.com.
