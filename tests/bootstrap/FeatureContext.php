<?php
use Behat\MinkExtension\Context\MinkContext;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
	Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\BrowserKitDriver,
	Behat\Mink\Driver\Selenium2Driver;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Features context.
 */
class FeatureContext extends MinkContext {
	/**
	 * Initializes context.
	 * Every scenario gets it's own context object.
	 *
	 * @param array $parameters context parameters (set them up through behat.yml)
	 */

	private $users, $subdomain, $multisite, $timeout;
	private $pluginsPageUrl, $themesPageUrl;

	public function __construct($parameters = NULL, $users = NULL) {
		if (is_array($parameters)) {
			foreach ($parameters as $key => $value) {
				// Convert snake_case parameter to camelCase before storing as variable
				$option = ucwords(str_replace(array('-', '_'), ' ', $key));
    			$option = str_replace(' ', '', $option);
				$option = lcfirst($option);
				$this->$option = $value;
			}
		}

		if (is_array($users)) {
			$this->users = $users;
		}
		$this->setDefaultParameters();
	}

    /**
     * Set default parameters if they are not set already
     *
     */
    public function setDefaultParameters() {
    	if ( ! isset($this->screenShotPath) ) {
		    $this->screenShotPath = "/root/screenshots/";
		}
        if ( ! isset($this->users) ) {
		    $this->users = [ 'admin' => 'admin'];
		}
        if ( ! isset($this->subdomain) ) {
		    $this->subdomain = false;
		}
        if ( ! isset($this->multisite) ) {
		    $this->multisite = true;
		}
        if ( ! isset($this->timeout) ) {
		    $this->timeout = 30;
		}
        if ( ! isset($this->pluginsPageUrl) ) {
		    $this->pluginsPageUrl = "wp-admin/plugins.php" ;
		}
        if ( ! isset($this->themesPageUrl) ) {
		    $this->themesPageUrl = "wp-admin/themes.php?default=1";
		}
    }

	/**
	 * Wordpress: Authenticates a user.
	 *
	 * @Given /^I am logged in as "([^"]*)" with the password "([^"]*)"$/
	 */
	public function iAmLoggedInAsWithThePassword( $username, $passwd ) {
		$this->getSession()->visit( $this->locatePath( 'wp-login.php' ) );
		// Log in
		$element = $this->getSession()->getPage();
		if ( empty( $element ) ) {
			throw new Exception( 'Page not found' );
		}

		$login_text = $element->findById( 'user_login' );
		// Wait for few seconds if element is not found due to page load issue
		if ( empty( $login_text ) ) {
			sleep( 3 );
		}

		$element->fillField( 'log', $username );
		try {
			$this->assertSession()->fieldValueEquals( 'log', $username );
		} catch ( Exception $e ) {
			$element->fillField( 'log', $username );
		}
		
		$element->fillField( 'pwd', $passwd );
		try {
			$this->assertSession()->fieldValueEquals( 'pwd', $passwd );
		} catch ( Exception $e ) {
			$element->fillField( 'pwd', $passwd );
		}
		
		$submit = $element->findButton( 'wp-submit' );
		if ( empty( $submit ) ) {
			throw new Exception( 'No submit button at ' . $this->getSession()->getCurrentUrl() );
		}
		$submit->click();
		
		$link = $this->getSession()->getPage()->findLink( "Dashboard" );
		// Wait for few seconds if element is not found due to page load issue
		if ( empty( $link ) ) {
			sleep( 3 );
			$link = $this->getSession()->getPage()->findLink( "Dashboard" );
		}
		if ( empty( $link ) ) {
			throw new Exception( 'Login failed at ' . $this->getSession()->getCurrentUrl() );
		}

		return;
	}

	/**
	 * Authenticates a user with password from configuration.
	 *
	 * @Given /^I am logged in as "([^"]*)"$/
	 */
	public function iAmLoggedInAs( $username ) {
		$password = $this->fetchPassword( $username );
		$this->iAmLoggedInAsWithThePassword( $username, $password );
	}

	/**
	 * Visit a given URL 
	 * 
	 * @Given /^I should visit "([^"]*)"$/
	 */
	public function iShouldVisit( $name ) {
		$this->visit( $this->locatePath( '/' . $name ) );
	}

	/**
	 * Helper function to fetch user passwords stored in behat config yml.
	 *
	 */
	public function fetchPassword( $name ) {
		try {
			$property = $this->users;
			$password = $property[ $name ];

			return $password;
		} catch ( Exception $e ) {
			throw new Exception( "Non-existent user/password for Username: $name please check config file." );
		}
	}

	/**
	 * Verifies if specified element has the correct value
	 *
	 * @Then /^"([^"]*)" element has value "([^"]*)"$/
	 */
	public function elementHasValue( $element, $value ) {
		$selector = $this->getSession()->getPage()->find( 'css', $element );
		if ( null === $selector ) {
			throw new \Exception( sprintf( 'Cannot find the element ' . $element ) );
		}
		$validate_value = $selector->getText();
		if ( stripos( $validate_value, $value ) === false ) {
			throw new \Exception( sprintf( 'Cannot find the ' . $element . ' with value ' . $value ) );
		}
	}

	/**
	 * Verifies if given element exists
	 *
	 * @Then /^"([^"]*)" element exists$/
	 */
	public function elementExists( $element ) {
		$selector = $this->getSession()->getPage()->find( 'css', $element );
		if ( null === $selector ) {
			throw new \Exception( sprintf( 'Cannot find the element ' . $element ) );
		}
	}

	/**
	 * Verifies if given element exists with HTML content
	 *
	 * @Then /^"([^"]*)" element exists with content$/
	 */
	public function elementExistsWithContent( $element ) {
		$selector = $this->getSession()->getPage()->find( 'css', $element );
		if ( null === $selector ) {
			throw new \Exception( sprintf( 'Cannot find the element ' . $element ) );
		}
		$content = $selector->getHTML();
		if ( empty( $content ) ) {
			throw new \Exception( sprintf( "Found element: %s, but does not have any html within it", $element ) );
		}
	}

	/**
	 * Verifies if given element with xpath exists
	 *
	 * TODO: Need to fix xpath arg regex
	 *
	 * @Then /^"([^"]*)" element with xpath exists$/
	 */
	public function elementWithXpathExists( $element ) {
		$selector = $this->getSession()->getPage()->findAll( 'xpath', $element );
		if ( null === $selector ) {
			throw new \Exception( sprintf( 'Cannot find the element ' . $element ) );
		}
	}

	/**
	 * Verifies if element with xpath has the correct value
	 *
	 * TODO: Need to fix xpath arg regex
	 *
	 * @Then /^"([^"]*)" element with xpath has value "([^"]*)"$/
	 */
	public function elementWithXpathHasValue( $element, $value ) {
		$selector = $this->getSession()->getPage()->findAll( 'xpath', $element );
		if ( null === $selector ) {
			throw new \Exception( sprintf( 'Cannot find the element ' . $element ) );
		}
		foreach ( $selector as $item ) {
			$validate_value = $item->getText();
			if ( stripos( $validate_value, $value ) === false ) {
				throw new \Exception( sprintf( 'Cannot find the ' . $element . ' with value ' . $value ) );
			}
		}
	}

	/**
	 * Wait for a given time in seconds
	 *
	 * @Then /^I wait (\d+) sec$/
	 */
	public function iWaitSec( $sec ) {
		sleep( $sec );
	}

	/**
	 * Enables Scroll View To Element with name attribute Functionality
	 *
	 * @Then /^scroll to element with class "([^"]*)"$/
	 */
	public function scrollToElementWithClass( $element_class ) {
		try {
			$js = sprintf( "document.getElementsByClassName(\"%s\")[0].scrollIntoView(true);", $element_class );
			$this->getSession()->executeScript( $js );
		} catch ( Exception $e ) {
			throw new \Exception( "ScrollIntoView failed" );
		}
	}

	/**
	 * Clicks on given element if it is visible in DOM
	 *
	 * @Then /^I click "([^"]*)" if visible$/
	 */
	public function iClickIfVisible( $element ) {
		$element = $this->getSession()->getPage()->find( 'css', $element );
		if ( $element != null ) {
			if ( $element->isVisible() ) {
				$element->click();
				sleep( 2 );
			}
		}

	}

	/**
	 * Clicks on an element
	 *
	 * @Then /^I click "([^"]*)"$/
	 */
	public function iClick( $element ) {
		try {
			$node = $this->getSession()->getPage()->find( 'css', $element );
			if ( $node == null ) {
				throw new Exception;
			}
		} catch ( Exception $e ) {
			throw new Exception( "$element not found" );
		}
		try {
			$node->click();
		} catch ( Exception $e ) {
			throw new Exception( "Cannot click $element" );
		}
	}

	/**
	 * Verifies if select Element has given value as selected
	 *
	 * @Then /^"([^"]*)" element is selected$/
	 */
	public function elementIsSelected( $element ) {
		$node = $this->getSession()->getPage()->find( 'css', $element );
		try {
			$isSelected = $node->getAttribute( 'selected' );

			if ( $isSelected != "selected" ) {
				throw new Exception;
			}
		} catch ( Exception $e ) {
			$value = $node->getText();
			throw new Exception( "$value is not selected" );
		}
	}

	/**
     * Returns associative array with key=value and value=label
     *
     */
    protected function getOptions($select) {
        $options = [];
        foreach ($select->findAll('xpath', '//option') as $option) {
            $label = $option->getText();
            $value = is_string($option->getAttribute('value')) ? $option->getAttribute('value') : $label;
            $options[$value] = $label;
        }
        return $options;
    }

	/**
	 * Verifies if a given option is selected for a select form element (drop down box)
	 * It compares the Text for selected option's value with given Text
	 *
	 * @Then /^"([^"]*)" select element has selected value "([^"]*)"$/
	 */
	public function selectElementHasSelectedValue( $element, $text ) {
		# Case1: Select Element not found
		try {
			$selectField = $this->getSession()->getPage()->findField( $element );
		} catch ( Exception $e ) {
			throw new Exception ( "$element element not found" );
		}
		try {
			$valueSelected = $selectField->getValue();
			$option=$this->getOptions($selectField);
			if ( $option[$valueSelected] != $text) {
			    throw new Exception();
            }
		} catch ( Exception $e ) {
			throw new Exception( "$element has $option[$valueSelected] selected. " );
		}
	}
	
  	/**
	 * Checks whether element is not visible ('display:none')
	 *
	 * @Then /^"([^"]*)" element is hidden$/
	 */
	public function elementIsHidden( $element ) {
		# Case1: element not found
		try {
			$node = $this->getSession()->getPage()->find( 'css', $element );
		} catch ( Exception $e ) {
			throw new Exception( "$element not found" );
		}
		# Case2: Unable to determine visibility or element already visible.
		try {
			$visibility = $node->isVisible();
			if ( $visibility == true ) {
				throw new Exception;
			}
		} catch ( Exception $e ) {
			throw new Exception( "$element is visible" );
		}
	}

	/**
	 * Checks whether the given element appears on the page periodically until timeout.
	 *
	 * @Then /^I wait for "([^"]*)" element$/
	 */
	public function iWaitForElement( $element ) {
		$selector = null;
		# loop and check whether the element is retrieved.
		for ( $i = 0; $i < ( $this->timeout / 2 ); $i ++ ) {
			# locate the element on given page
			$selector = $this->getSession()->getPage()->find( 'css', $element );
			# Check whether the xpath of element is retreived in $selector
			if ( $selector !== null ) {
				break;
			} # element  not found momentarily
			else {
				sleep( 2 );
			}
		}
		# assert whether element is found
		if ( is_null( $selector ) ) {
			throw new Exception( "$element does not exist" );
		}
	}

	/**
	 * Checks whether the given element appears on the page periodically until timeout
	 *
	 * It implements findField() which directlysearches for form elements (input, textarea or select)
	 * Use it when css selector has nth child because form field's name, label or id is unique
	 *
	 * @Then /^I wait for "([^"]*)" form element$/
	 */
	public function iWaitForFormElement( $element ) {
		$formFieldselector = null;
		for ( $i = 0; $i < ( $this->timeout / 2 ); $i ++ ) {
			$formFieldselector = $this->getSession()->getPage()->findField( $element );
			if ( $formFieldselector !== null ) {
				break;
			} else {
				sleep( 2 );
			}
		}
		if ( is_null( $formFieldselector ) ) {
			throw new Exception( "$element does not exist" );
		}
	}

	/**
	 * Checks whether the given text appears on the page periodically until timeout
	 *
	 * It fetches the text of the entire page and checks whether the given text is present.
	 *
	 * @Then /^I wait for "([^"]*)" text$/
	 */
	public function iWaitForText( $text ) {
		$pageTextContent = null;
		for ( $i = 0; $i < ( $this->timeout / 2 ); $i ++ ) {
			$pageTextContent = $this->getSession()->getPage()->getText();
			# check whether page text is not null and given text exists in the page text
			if ( $pageTextContent !== null && strpos( $pageTextContent, $text ) !== false ) {
				break;
			}
			else {
				sleep( 2 );
			}
		}
		# assert whether given text is present in the page text.
		if ( strpos( $pageTextContent, $text ) == false ) {
			throw new Exception( "$text does not appear anywhere on the page" );
		}
	}

	/**
	 * Wordpress: Verifies if plugin is installed from Network Admin Plugins page
	 *
	 * @Then /^"([^"]*)" plugin is installed$/
	 */
	public function pluginIsInstalled( $plugin_name ) {
		$this->getSession()->visit( $this->locatePath( 'wp-admin/network/plugins.php' ) );
		$field       = 'plugin-search-input';
		$field       = $this->fixStepArgument( $field );
		$plugin_name = $this->fixStepArgument( $plugin_name );
		$this->getSession()->getPage()->fillField( $field, $plugin_name );
		sleep( 2 );
		$this->getSession()->wait( 5000, 'jQuery.active === 0' );
		$selector    = $this->getSession()->getPage()->findById( 'the-list' );
		$plugin_info = $selector->getText();
		if ( stripos( $plugin_info, "No plugins found for" ) !== false ) {
			throw new Exception( sprintf( 'Cannot find "%s" in Network Admin Plugins page ', $plugin_name ) );
		}
	}

	/**
	 * Wordpress: Verifies if plugin is network activated from Network Admin Plugins page
	 *
	 * @Then /^"([^"]*)" plugin is network activated$/
	 */
	public function pluginIsNetworkActivated( $plugin_name ) {
		$this->pluginIsInstalled( $plugin_name );
		$pluginactivationfield = $this->getSession()->getPage()->find( 'css', 'td[class="plugin-title column-primary"]' );
		$plugintest            = $pluginactivationfield->getText();
		if ( stripos( $plugintest, $plugin_name ) === false ) {
			throw new Exception( sprintf( 'Cannot find "%s" in Network Admin Plugins page ', $plugin_name ) );
		}
		if ( stripos( $plugintest, "Network Deactivate" ) === false ) {
			throw new Exception( sprintf( '"%s" is not network activated', $plugin_name ) );
		}
	}

	/**
	 * Wordpress: Verifies if the user control for a plugin in the Plugin Management is enabled.
	 *
	 * @Then /^"([^"]*)" plugin is available for "([^"]*)"$/
	 */
	public function pluginIsAvailableFor( $plugin_folder_name, $user_control ) {
		$this->getSession()->visit( $this->locatePath( 'wp-admin/network/plugins.php?page=plugin-management' ) );
		$row = $this->getSession()->getPage()->find( 'css', 'select[name*="' . $plugin_folder_name . '"]' );

		$selected_element = $row->find( 'css', 'option[selected="yes"]' );
		if ( strcasecmp( $selected_element->getText(), $user_control ) != 0 ) {
			throw new Exception( "User Control '" . $user_control . "' is not set for plugin " . $plugin_folder_name );
		}
	}

	/**
	 * Wordpress: Verifies if plugin is activated on site
	 *
	 * @Then /^"([^"]*)" plugin is activated$/
	 */
	public function pluginIsActivated( $plugin_name ) {
		$field       = 'plugin-search-input';
		$field       = $this->fixStepArgument( $field );
		$plugin_name = $this->fixStepArgument( $plugin_name );
		$this->getSession()->getPage()->fillField( $field, $plugin_name );
		sleep( 2 );
		$selector    = $this->getSession()->getPage()->findById( 'the-list' );
		$plugin_info = $selector->getText();
		if ( stripos( $plugin_info, "Deactivate" ) === false ) {
			throw new Exception( sprintf( '"%s" is not activated', $plugin_name ) );
		}
	}

	/**
	 * Wordpress: Logs out a logged session from Dashboard.
	 *
	 * @Then /^I log out$/
	 */
	public function iLogOut() {
		$logout_url = $this->getSession()->getPage()->find( 'css', 'li#wp-admin-bar-logout > a' )->getAttribute( 'href' );

		if ( null === $logout_url ) {
			throw new \InvalidArgumentException( sprintf( 'Cannot find logout url' ) );
		}
		$this->visit( $logout_url );
	}

	/**
	 * Wordpress: Activates a theme on given site. (Uses Default Wordpress Theme Page)
	 *
	 * TODO: Need to fix activating incorrect theme when search results in more than 1 options
	 *
	 */
	public function iActivateThemeUsingDefaultThemePage( $theme_name ) {
		$fieldCssSelector    = 'wp-filter-search-input';
		$activateCssSelector = '#wpbody-content > div.wrap > div.theme-browser.rendered > div > div > div.theme-actions > a.button.activate';
		$field               = $this->fixStepArgument( $fieldCssSelector );
		$theme_name          = $this->fixStepArgument( $theme_name );
		$this->getSession()->getPage()->fillField( $field, $theme_name );
		sleep( 2 );
		$themeStatus   = "h2.theme-name";
		$active        = $this->getSession()->getPage()->find( 'css', $themeStatus )->getText();
		$currentStatus = 'Active:';
		if ( stripos( $active, $currentStatus ) !== false ) {
			return;
		} else {
			try {
				$activateButton = $this->getSession()->getPage()->find( 'css', $activateCssSelector );
				if ( $activateButton == null ) {
					throw new Exception;
				}
			} #Case1: Theme does not exist,hence activate button not found
			catch ( Exception $e ) {
				throw new \Exception( "Theme not found" );
			}
			try {
				$activateButton->click();
			} #Case2: Unable to click Activate Button
			catch ( Exception $e ) {
				throw new \Exception ( "$theme_name cannot be activated" );
			}
		}
	}

	/**
	 * Wordpress: Activates a theme on given site.
	 *
	 * TODO: Need to fix activating incorrect theme when search results in more than 1 options
	 *
	 * @Then /^I activate theme "([^"]*)"$/
	 */
	public function iActivateTheme( $theme_name ) {
		$siteAddress   = $this->getCurrentSiteUrl();
		$themesPageUrl = $siteAddress . $this->themesPageUrl;
		$this->getSession()->visit( $themesPageUrl );
		$currentUrl        = $this->getSession()->getCurrentUrl();
		$multisiteThemeUrl = 'page=multisite-theme-manager.php';
		if ( stripos( $currentUrl, $multisiteThemeUrl ) ) {
			$this->iActivateThemeUsingMultisiteThemeManager( $theme_name );
		} else {
			$this->iActivateThemeUsingDefaultThemePage( $theme_name );
		}
	}

	/**
	 * Wordpress: Activates default theme on given site.
	 *
	 * @Then /^I activate default theme$/
	 */
	public function iActivateDefaultTheme() {
		$defaultTheme = 'Twenty Sixteen';
		$this->iActivateTheme( $defaultTheme );
	}

	/**
	 * Wordpress: Activates a plugin on given site.
	 *
	 * TODO: Consider the case when search results have more than 1 option
	 *
	 * @Then /^I activate plugin "([^"]*)"$/
	 */
	public function iActivatePlugin( $plugin_name ) {
		$fieldCssSelector    = 'plugin-search-input';
		$activateCssSelector = '#the-list > tr > td.plugin-title.column-primary > div > span.activate > a';
		$siteAddress = $this->getCurrentSiteUrl();
		$pluginsPageUrl = $siteAddress . $this->pluginsPageUrl;
        $this->getSession()->visit( $pluginsPageUrl );
		$field               = $this->fixStepArgument( $fieldCssSelector );
		$this->getSession()->getPage()->fillField( $field, $plugin_name );
		$this->iWaitForElement('#the-list');
		$selector = $this->getSession()->getPage()->findById( 'the-list' );
		$plugin_info  = $selector->getText();
		$pluginStatus = 'Deactivate';
		# Case1: Plugin is already activated
		if ( strpos( $plugin_info, $pluginStatus ) !== false ) {
			return;
		} else { 
			# Case2: Plugin does not exist , hence activate button not found
			try {
				$activateButton = $this->getSession()->getPage()->find( 'css', $activateCssSelector );
				if ( $activateButton == null ) {
					throw new Exception;
				}
			} catch ( Exception $e ) {
				throw new Exception ( "$plugin_name not found" );
			} 
			# Case3: Unable to click activate button
			try {
				$activateButton->click();
			} catch ( Exception $e ) {
				throw new Exception( "$plugin_name cannot be activated" );
			}
		}
	}

	/**
	 * Wordpress: Deactivates a plugin on given site
	 *
	 * @Then /^I deactivate plugin "([^"]*)"$/
	 */
	public function iDeactivatePlugin( $plugin_name ) {
		$fieldCssSelector      = 'plugin-search-input';
		$deactivateCssSelector = '#the-list > tr > td.plugin-title.column-primary > div > span.deactivate > a';
        $siteAddress = $this->getCurrentSiteUrl();
        $pluginsPageUrl = $siteAddress . $this->pluginsPageUrl;
        $this->getSession()->visit( $pluginsPageUrl );
		$field                 = $this->fixStepArgument( $fieldCssSelector );
		$this->getSession()->getPage()->fillField( $field, $plugin_name );
		sleep( 2 );
		$selector     = $this->getSession()->getPage()->findById( 'the-list' );
		$plugin_info  = $selector->getText();
		$pluginStatus = 'Activate';
		# Case1: Plugin is already deactivated
		if ( strpos( $plugin_info, $pluginStatus ) !== false ) {
			return;
		} else { 
			# Case2: Plugin does not exist , hence deactivate button not found
			try {
				$deactivateButton = $this->getSession()->getPage()->find( 'css', $deactivateCssSelector );
				sleep( 2 );
				if ( $deactivateButton == null ) {
					throw new Exception;
				}
			} catch ( Exception $e ) {
				throw new Exception ( "$plugin_name already activated" );
			} 
			# Case3: Unable to click Deactivate button
			try {
				$deactivateButton->click();
			} catch ( Exception $e ) {
				throw new Exception( "$plugin_name cannot be deactivated" );
			}
		}
	}

	/**
     * Wordpress: Find sitename for wordpress multisite sub directory
     *
     */
    public function getCurrentSiteUrl()
    {
        $currentUrl = $this->getSession()->getCurrentUrl();
        $parsedUrl = parse_url($currentUrl);
        $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
        $host = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
        $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
        $user = isset($parsedUrl['user']) ? $parsedUrl['user'] : '';
        $pass = isset($parsedUrl['pass']) ? ':' . $parsedUrl['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
        # pivot String
        $adminStringInUrl = "wp-admin";
        # Get site url for subdirectory multisite
        if ($this->subdomain == false) {
            # Check whether wp-admin present in urlPath and extract the part aftr hostUrl and before wp-admin.
            if ((mb_strpos($path, $adminStringInUrl) !== false)) {
                $siteName = strstr($path, $adminStringInUrl, true);
            } # Extracts the part in urlPath present between first two forward slashes.
            else {
                # Split the path into component strings
                $pathComponents = explode('/', $path);
                if ($pathComponents[1] !== null) {
                    $siteName = '/' . $pathComponents[1] . '/';
                } else {
                    $siteName = '/';
                }
            }
        } # Get site url for subdomain multisite
        else {
            $siteName = '/';
        }
        $siteAddress = $scheme . $user . $pass . $host . $port . $siteName;
        return ($siteAddress);
    }

	/**
	 * Plugins: Activates a theme on given site. (Uses WPMU Multisite Theme Manager)
	 *
	 * TODO: Need to fix activating incorrect theme when search results in more than 1 options
	 */
	public function iActivateThemeUsingMultisiteThemeManager( $theme_name ) {
		$fieldCssSelector    = 'theme-search-input';
		$activateCssSelector = '#wpbody-content > div.wrap > div.theme-browser.rendered > div > div > div.theme-actions > a.button.button-primary.activate';
		$field               = $this->fixStepArgument( $fieldCssSelector );
		$theme_name          = $this->fixStepArgument( $theme_name );
		sleep(2);
		$this->getSession()->getPage()->fillField( $field, $theme_name );
		sleep( 2 );
		$themeStatusHeader = "h3.theme-name";
		$active            = $this->getSession()->getPage()->find( 'css', $themeStatusHeader )->getText();
		#Looks for the string 'Active:' in the themeStatusHeader, ex: Active:Canard
		$currentStatus = 'Active:';
		if ( stripos( $active, $currentStatus ) !== false ) {
			return;
		} else {
			try {
				$activateButton = $this->getSession()->getPage()->find( 'css', $activateCssSelector );
				if ( $activateButton == null ) {
					return;
				}
			} #Case1: Theme does not exist,hence activate button not found
			catch ( Exception $e ) {
				throw new \Exception( "Theme not found" );
			}

			try {
				$activateButton->click();
			} #Case2: Unable to click Activate Button
			catch ( Exception $e ) {
				throw new \Exception ( "$theme_name cannot be activated" );
			}
		}
	}
}

