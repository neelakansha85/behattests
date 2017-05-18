<?php
use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//
use PHPUnit_Framework_Assert as Assert;

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

	public function __construct( array $parameters ) {
		$this->users       = $parameters['users'];
	}

	/**
	 * Authenticates a user.
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
	public function iShouldVisitSite( $name ) {
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
	 * Admin: Verifies if plugin is installed from Network Admin Plugins page
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
		$selector    = $this->getSession()->getPage()->findById( 'the-list' );
		$plugin_info = $selector->getText();
		if ( stripos( $plugin_info, "No plugins found for" ) !== false ) {
			throw new Exception( sprintf( 'Cannot find "%s" in Network Admin Plugins page ', $plugin_name ) );
		}
	}

	/**
	 * Admin: Verifies if plugin is network activated from Network Admin Plugins page
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
	 * Admin: Verifies if plugin is activated on site
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
	 * Admin: Logs out a logged session from Dashboard.
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
	 * Wait for given time
	 *
	 * @Then /^I wait (\d+) sec$/
	 */
	public function wait( $sec ) {
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
}
