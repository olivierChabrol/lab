# lab
wordpress pluggin for i2m lab

### Authors

### Citation
We reuse code from :
* [Baptiste Blondelle](https://profiles.wordpress.org/friz/) from the [HAL pluggin](https://fr.wordpress.org/plugins/hal/)
* [Marcus Sykes](https://profiles.wordpress.org/netweblogic/) and [nutsmuggler](https://profiles.wordpress.org/nutsmuggler/)  from the [Event manager pluggin](https://wordpress.org/plugins/events-manager/)

### Installation

### Features

## Shortcodes
### Directory


[lab-directory] Display all user in lab

[lab-directory] display-left-user=false Display only user present in lab (not declare as left)

[lab-directory] display-left-user=true Display only left user

[lab-directory group=AA] Display user associated to a group (the acronym group is use) with alphabet letters for search

[lab-directory group=AA all-group=true] Display user associated to a group (the acronym group is use) without alphabet letters for search

### Profile
[lab-profile]
### Event
[lab-old-event {slug} {year}] Display past events for a given year (optional)
##### Use
To search a category OR another category :
* slug='categoryName1, categoryName2...'
To search a category AND another category :
* slug='categoryName1+categoryName2...'

You can add as more category as you want but there must be at least one.

### HAL
[lab-hal group=AA] Display hal records associated to a group (the acronym group is use)