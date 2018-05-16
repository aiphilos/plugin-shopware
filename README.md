# VerignAiPhilosSearch
## About VerignAiPhilosSearch
This plugin provides an implementation of the [aiPhilos](https://aiphilos.com) product search for the [Shopware](https://shopware.com/) eCommerce platform.

It provides synchronization between the Shopware product database and the aiPhilos database. It supports multiple shops and languages (one DB per shop/language, aiPhilos itself only supports German so far).

Search results are retrieved via the aiPhilos API but Shopware's default DBAL search is still used and required.

Compatibility with other plugins that decorate or replace the search is not guaranteed.  

## License

Provided under the terms and conditions of the GNU GPLv3

Please see [License File](LICENSE.md) for more information.

## Installation

First make sure you are running at least Shopware Version 5.2.0.

Zip the contents of this folder and upload it to your Shopware installation via plugin manager.

From there, the plugin can be installed, activated and uninstalled like any other plugin.

### Note for Shopware versions < 5.2.15

The Shopware 5.2 plugin system was unable to install cronjobs from the included cronjob.xml. You can fix this manually by going to "Configuration > Basic settings > System > Cronjobs" in the Shopware backend. There you can create a new cron job by clicking the "Add entry" button, name it "Update aiPhilos databases" or something similar and enter "Shopware_CronJob_VerignAiPhilosSearchSyncDatabase" as the action. Set it to run at least once a day but don't activate it before you haven't properly configured the plugin (see below).

## Configuration

### Shopware configuration

To not use up your search requests with aiPhilos it is highly recommended that you set the minimum search term length higher than Shopware's default 3 characters to a minimum of 5.
This is especially important because of the AJAX live search that starts searching as soon as the user starts typing which will use up your search queries too quickly and not produce good results with just three characters.

You can find this option under "Configuration > Basic Settings > Frontend > Search" under the confusing name "Maximum search term length" (it actually correctly means the minimum).


### Plugin configuration
Open the plugin configuration in Shopware's plugin manager.
You will find the following configuration options.

* Use AI search for this shop?

This setting determines whether or not the AI search is active for the given subshop. Since aiPhilos currently only supports German, you should explicitly deactivate it for every subshop that isn't German.

* aiPhilos Username

The username provided by aiPhilos.
Shared between all subshops.

* aiPhilos Password

The password provided by aiPhilos for your username.
Shared between all subshops.

* aiPhilos Database Name

The name of the aiPhilos database used by the given subshop. 
This must be a unique name comprised of only upper and lower case letters from the English alphabet numbers and underscores. No shops must share the same database. 
The database does not need to exist before being entered here, non-existent databases will be created.

* Number of months for bestsellers

To accurately judge search queries for popular and bestselling items aiPhilos needs to be provided a measurement for that, this plugin uses sales over a given period of time.
Enter the number of months that should be considered for this measurement here.

* Attribute Columns

You can optionally also provide a semicolon separated list of article attributes (free text fields) that should also be send to the aiPhilos database. To add columns, enter them here exactly as they appear under column name in Shopware's free text field management for the table "s_articles_attributes".
Let's say you want to add the columns "Comment" stored as the column "attr1" and "Additional Description" stored as column "additional_description".
In that case the input for this field must look like this "attr1;additional_description".

Only use columns here that contain human readable text in the language that your subshop uses. It is sufficient to use Shopware's translation feature for free text fields for this but if you use one column per translation you can also simply configure each subshop with the appropriate column.

* Excluded Category-IDs

With this option you can exclude article within some categories from being sent to the aiPhilos database by entering the category-IDs as a semicolon separated list.

This is useful if some third party plugins use categories to supply their functionality but which don't contain sensible article data.

You can find the category-ID by clicking the desired category in Shopware's category manager and using the number that says "System-ID" next to it.

It is not necessary to exclude categories from different subshops or blog categories manually, as they are excluded automatically.

When excluding a category all it's child-categories will be excluded as well.

* Fallback Mode

This option let's you configure if and under what conditions the search should fall back to Shopware's default search.
Note: Due to bugs in Shopware these options might be displayed in German even when using the backend with the English language. The order of options here reflects the order of options in the backend.

__Never (not recommended)__

Never fall back to Shopware's default search under any circumstances. Only recommended for development, don't use this in production Shops.

__Errors and no results (Default)__

Fall back to the default search when either no results are found by aiPhilos or an error occurs. This is the default setting.

__Only on errors (minimal recommended)__

Only fall back when an error occurs during the attempted aiPhilos search. This is the recommended minimum setting and especially useful once aiPhilos has fully learned your article data and the results have become good enough that you can be certain that if aiPhilos finds nothing, nothing is the correct result.

__Only when no results returned__

Only fall back if aiPhilos returns no results.
This option exists mostly for the sake of completeness.

* Learning Mode

Learning mode is best used when introducing aiPhilos search to an already existing Shopware shop. If it is activated search queries will not be sent to aiPhilos and instead, the default search is used. The rest of the plugin remains active for that subshop so the aiPhilos article database will be updated with your article data so aiPhilos can start learning your data. You can "peek" into what result aiPhilos would return while in Learning Mode by adding "&forceAi" to your search queries manually.

For example your shop is hosted on "www.myshop.local" and you want to search for "apple" then going to the URL "www.myshop.local/search?sSearch=apple&forceAi" would force the aiPhilos search to be used.

### Cronjob Configuration

Once you have configured the plugin to your liking and your article data is in a reasonable shape - meaning your descriptions do not contain nonsensical or blind text like "Lorem Ipsum" - it is time to activate and execute the "Update aiPhilos databases" cronjob.

This cronjob should run at least once a day but it might be advisable to run it more often if your article data is still in a state of fast change so the aiPhilos database mimics the Shopware database more closely.

### Troubleshooting

This plugin uses Shopware's logging system in most if not all critical spots of its components. If you are encountering issues while operating it make sure to first check the log files under "Configuration > Logfile > System Log". There you can select the file starting with "verign_ai_philos_search" and the appropriate date.
Make sure to click the magnification glass to see the most the detailed information provided in the context field.