# silverstripe-vk-connect
Integration of Vkontakte Connect into SilverStripe

## Maintainer Contact 
 * Nurgazy Sarbalaev 
   <archantyrael (at) gmail (dot) com>
	
## Requirements
 * SilverStripe 3.1

## Overview

The module provides a **basic** interface which allows users to login to your 
website using their Vkontakte account details, creating a single sign-on within 
the existing SilverStripe member system.

## Installation

composer require "rchntrl/silverstripe-vkconnect" "dev-master"
```

[Register your website / application](http://vk.com/editapp?act=create)
with vk.com.

Set your configuration through the SilverStripe Config API. For example I keep
my configuration in `mysite/_config/vkconnect.yml` file:

```
VkControllerExtension:
  app_id: 'MyAppID'
  api_secret: 'Secret'
```

Update the database by running `/dev/build` to add the additional fields to 
the `Member` table and make sure you `?flush=1` when you reload your website.

```
<a href="$VkLoginLink">Login via Vk</a>
```

### Options

All the following values are set either via the Config API like follows

  Config::inst()->update('VkControllerExtension', '$option', '$value')

Or (more recommended) through the YAML API 

  VkControllerExtension:
    option: value

### app_id

Your app id. Found on the VK Developer Page.

### api_secret

VK API secret. Again, from your Application page.

### create_member 

  Optional, default: true

Whether or not to create a `Member` record in the database with the users 
information. If you disable this, ensure your code uses $CurrentVkMember
rather than $Member. Other access functionality (such as admin access) will not
work.

### member_groups

  Optional, default ''
	
A list of group codes to add the user. For instance if you want every member who
joins through VK to be added to a group `VK Members` set the 
following:

  VkControllerExtension:
    member_groups:
      - vk_members

### permissions

  Optional, default 'email'


## License

Released under the BSD-3-Clause License. 
