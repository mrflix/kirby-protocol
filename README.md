# Kirby – Protocol section

Keep an overview of that's happening with the protocol section for Kirby CMS.

![Screenshot of the protocol section in a ](https://user-images.githubusercontent.com/60777/223475679-3b606edd-773c-4fc0-90c9-063c108a33cc.png)


## 1. The Page Method

Log actions using the `protocol` page method:

```php
$page->protocol('created');
$page->protocol('new-password', $old_password, $new_password);
$page->protocol('invitation', $page->contact_email(), $page->password());
$page->parent()->protocol('form.edited', $page, $diff);
```

The data get's stored in the page `protocol()` is being called from. The Parameters for the method are:

| Option | Type   | Description               |
|:-------|:-------|:--------------------------|
| action | String | What happend? Required.   |
| param1 | Mixed  | First optional parameter  |
| param2 | Mixed  | Second optional parameter |

`param1` and `param2` can be `File`, `User` and `Page` objects. They get turned into panel links.


## 2. Options

```php
return array(
  'mrflix.protocol.actions' => [], # required
  'mrflix.protocol.user'    => null,
  'mrflix.protocol.time'    => 'HH:mm',
  'mrflix.protocol.date'    => 'l, j. F Y' # 'cccc, d. LLLL yyyy' when 'date.format' is 'intl'
  'mrflix.protocol.limit'   => 400,
);
```


### 2.1 `actions` (required)

Define how the actions you're tracking should get displayed:

```php
return [
  'mrflix.protocol.actions' => [
    'created' => [
      'icon' => 'add',
      'message' => '{{ user }} created {{ page }}'
    ],
    'invitation' => [
      'icon' => 'email',
      'message' => '{{ user }} sent an invitation for {{ page }}',
      'detail' => 'To {{ param1 }} with the password {{ param2 }}'
    ],
    'new-password' => [
      'icon' => 'key',
      'message' => '{{ user }} created a new password for {{ page }}',
      'detail' => '{{ param1 }} → {{ param2 }}'
    ],
    'form.edited' => [
      'icon' => 'edit',
      'message' => '{{ user }} edited the form {{ param1 }} for the client {{ page }}',
      'detail' => function($data){
        return (count($data->param2()->value()) > 1 ? 'Fields' : 'Field') .': '. implode(', ', $data->param2()->value());
      }
    ],
  ]
]
```

#### 2.1.1 `actions` Definition

| Option  | Type   | Description                                                   |
|:--------|:-------|:--------------------------------------------------------------|
| icon    | String | [Kirby Icon](https://getkirby.com/docs/reference/panel/icons) |
| message | Mixed  | Main textline                                                 |
| detail  | Mixed  | Optional detail description                                   |

`message` and `detail` can be callbacks.

#### 2.1.2 `actions` Variables

The following variables can be used as variables:

| Option | Type   | Default        | Description          |
|:-------|:-------|:---------------|:---------------------|
| user   | Mixed  | logged in user | Who made the change? |
| page   | Object |                | On which page?       |
| param1 | Mixed  | `""`           | First parameter      |
| param2 | Mixed  | `""`           | Second parameter     |

`File`, `User` and `Page` variables get turned into panel links.


### 2.2 `user`

I built this plugin to keep track of changes that invited frontend-editors make. Since the editors are not logged in as kirby users, their changes get stored using the kirby user. Using this option its possible to enrich the user info with data from a field:

```php
'mrflix.protocol.user' => function($page){
  if(kirby()->user()->isKirby()){
    return $page->editor_email();
  } else {
    return kirby()->user();
  }
},
```

### 2.3 `time` and `date`

You can customize the `time` and `date` format. Based on the `date.handler` that you set (which per default is `date`).


### 2.4 `limit`

I didn't feel the need to add pagination yet so the protocol cut's off at the arbitrary number of `400` entries. Feel free to change this value.


## 3. Displaying the data

You can include the protocol section in pages on which you gather protocol data and on their parent pages.
On parent pages the protocols of all children get combined.

```yaml
sections:
  protocol:
    type: protocol
```

## 4. Public Whishlist

If you like one of the following features and have the time and skills to implement them?

- [ ] database support
- [ ] pagination

Then contact me so that we won't work on it at the same time and eventually send a pull request.

## 5. License

This plugin is free and published under the MIT license.
