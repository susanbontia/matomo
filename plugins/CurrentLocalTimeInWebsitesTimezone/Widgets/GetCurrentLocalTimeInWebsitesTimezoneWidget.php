<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CurrentLocalTimeInWebsitesTimezone\Widgets;

use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;
use Piwik\Common;
use Piwik\Site;
use Piwik\Date;

/**
 * This class allows you to add your own widget to the Piwik platform. In case you want to remove widgets from another
 * plugin please have a look at the "configureWidgetsList()" method.
 * To configure a widget simply call the corresponding methods as described in the API-Reference:
 * http://developer.piwik.org/api-reference/Piwik/Plugin\Widget
 */
class GetCurrentLocalTimeInWebsitesTimezoneWidget extends Widget
{
    public static function configure(WidgetConfig $config)
    {
        /**
         * Set the category the widget belongs to. You can reuse any existing widget category or define
         * your own category.
         */
        $config->setCategoryId('CurrentLocalTimeInWebsitesTimezone_AboutMatomo');

        /**
         * Set the subcategory the widget belongs to. If a subcategory is set, the widget will be shown in the UI.
         */
        // $config->setSubcategoryId('General_Overview');

        /**
         * Set the name of the widget belongs to.
         */
        $config->setName('CurrentLocalTimeInWebsitesTimezone_CurrentLocalTimeInWebsitesTimezoneWidget');

        /**
         * Set the order of the widget. The lower the number, the earlier the widget will be listed within a category.
         */
        $config->setOrder(99);

        /**
         * Optionally set URL parameters that will be used when this widget is requested.
         * $config->setParameters(array('myparam' => 'myvalue'));
         */

        /**
         * Define whether a widget is enabled or not. For instance some widgets might not be available to every user or
         * might depend on a setting (such as Ecommerce) of a site. In such a case you can perform any checks and then
         * set `true` or `false`. If your widget is only available to users having super user access you can do the
         * following:
         *
         * $config->setIsEnabled(\Piwik\Piwik::hasUserSuperUserAccess());
         * or
         * if (!\Piwik\Piwik::hasUserSuperUserAccess())
         *     $config->disable();
         */
    }

    /**
     * This method renders the widget. It's on you how to generate the content of the widget.
     * As long as you return a string everything is fine. You can use for instance a "Piwik\View" to render a
     * twig template. In such a case don't forget to create a twig template (eg. myViewTemplate.twig) in the
     * "templates" directory of your plugin.
     *
     * @return string
     */
    public function render()
    {
        // or: return $this->renderTemplate('myViewTemplate', array(...view variables...));
        // return '<div class="widgetBody">Hello World! My Widget Text</div>';

        // Get the local current system time
        $localCurrentTimezone = date_default_timezone_get();
        date_default_timezone_set($localCurrentTimezone);
        $localCurrentTime = date("Y/m/d H:i:s A");

        $idSite  = Common::getRequestVar('idSite', 0, 'int');
        $siteTimezone = Site::getTimezoneFor($idSite);
        date_default_timezone_set($siteTimezone);
        $siteCurrentTime = date("Y/m/d H:i:s A");
            // print_r("<br/>Site Timezone: " .$siteTimezone);
            // print_r("<br/>Site Time: " . $siteCurrentTime);

            return '
            <div class="widgetBody" >
               
                <div id="currentTimeContainer" style="display: block;">
                    <p style="font-weight: bold;">Current Local Time</p>
                    <p id="currentTimeId" style="padding-left: 20px">' . $localCurrentTime . '</p>
                </div>
                <div id="siteCurrentTimeContainer" style="display: none;">
                    <p style="font-weight: bold;">Site Local Time</p>
                    <p id="siteCurrentTimeId" style="padding-left: 20px">' . $siteCurrentTime . '</p>
                </div>
            </div>
            <script>

                function updateElementTime(elementId, formatLocalTime, timezone) {
                    var now = new Date();
                    var formattedTime;

                    if (formatLocalTime) {
                        // Format time as local time
                        var hours = now.getHours();
                        var minutes = now.getMinutes();
                        if (minutes < 10) {
                            minutes = "0" + minutes;
                        }

                        var formattedTime = now.getFullYear() + "/" +
                                            ("0" + (now.getMonth() + 1)).slice(-2) + "/" +
                                            ("0" + now.getDate()).slice(-2) + " " +
                                            ("0" + hours).slice(-2) + ":" + minutes;

                    } else if (timezone) {
                        // Format time according to the given timezone
                        var options = {
                            year: "numeric",
                            month: "2-digit",
                            day: "2-digit",
                            hour: "2-digit",
                            minute: "2-digit",
                            hour12: false,
                            timeZone: timezone
                        };

                        var formatter = new Intl.DateTimeFormat([], options);
                        var formattedDate = formatter.format(now).split(", ");
                        var datePart = formattedDate[0].split("/");
                        var timePart = formattedDate[1];

                        formattedTime = datePart[2] + "/" + datePart[1] + "/" + datePart[0] + " " + timePart;

                    }

                    // Update the elements text content with the formatted time
                    var element = document.getElementById(elementId);
                    if (element) {
                        element.textContent = formattedTime;
                    }

                }

                // Function to start updating time periodically for a specific element with a specific timezone or local time
                function startUpdatingTime(elementId, formatLocalTime, timezone) {
                    updateElementTime(elementId, formatLocalTime, timezone);
                    setInterval(function() {
                        updateElementTime(elementId, formatLocalTime, timezone);
                    }, 60000);
                }

                // Start updating time for specific elements
                startUpdatingTime("siteCurrentTimeId", false, "' . $siteTimezone . '"); // Update with timezone
                startUpdatingTime("currentTimeId", true); // Update with local time
                

                // Compare the text content of two elements and display the appropriate containers    
                var currentTimeText = document.getElementById("currentTimeId").textContent.trim();
                var siteCurrentTimeText = document.getElementById("siteCurrentTimeId").textContent.trim();

                if (currentTimeText === siteCurrentTimeText) {
                    document.getElementById("currentTimeContainer").style.display = "block";
                    document.getElementById("siteCurrentTimeContainer").style.display = "none";
                } else {
                    document.getElementById("currentTimeContainer").style.display = "block";
                    document.getElementById("siteCurrentTimeContainer").style.display = "block";
                }

            </script>


        ';
       
    }
}
