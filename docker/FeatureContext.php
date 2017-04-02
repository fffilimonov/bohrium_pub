<?php

use Behat\Behat\Context\ClosuredContextInterface;
use Behat\Behat\Context\TranslatedContextInterface;
use Behat\Behat\Context\BehatContext;
use Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Behat\Hook\Scope\AfterStepScope;

/**
 * Features context.
 */
class FeatureContext extends MinkContext
{

  private $step_counter = 0;
  private $redis = null;
  private $sid = null;

  /**
   * @BeforeScenario
   */
  public function prepare()
  {
    $this->sid = getenv('SID');
    $this->redis = new Redis();
    $this->redis->connect('redis-scens', 6379);
  }

  /**
   * @Then /^(?:|I )open "(?P<page>[^"]+)"$/
   */
  public function visit($page)
  {
    $this->getSession()->resizeWindow(1280, 1024, 'current');
    $this->visitPath($page);
    $this->waitPageLoaded();
  }

  /**
   * @AfterStep
   */
  public function takeScreenShotAfterStep(afterStepScope $scope)
  {
    $this->step_counter++;
    $driver = $this->getSession()->getDriver();
    $data = $this->getSession()->getDriver()->getScreenshot();
    $base64 = 'data:image/png;base64,' . base64_encode($data);

    $data = array($scope->getStep()->getText() => $base64);

    $this->redis->append($this->sid, "," . json_encode($data));
  }

  /**
    * Click some text
    *
    * @Then /^I click on the text "([^"]*)"$/
    */
  public function iClickOnTheText($text)
  {
    $doc = $this->getSession()->getPage();
    $all = $doc->findAll('xpath', "//*[contains(text(), \"$text\")]");

    $elements = array_filter($all, function ($element) {
      return $element->isVisible();
    });
    $elements = array_values($elements);

    if (count($elements) < 1) {
        throw new \InvalidArgumentException(sprintf('Cannot find text: "%s"', '$text'));
    }

    $this->clickElement($elements[0]);
  }

  /**
    * Fill nth visible input
    *
    * @Then I fill :n input with :text
    */
  public function iFillVisibleInput($n, $text)
  {
    $number = intval($n) - 1;
    $doc = $this->getSession()->getPage();
    $all = $doc->findAll('css', 'input');

    $elements = array_filter($all, function ($element) {
      return $element->isVisible();
    });
    $elements = array_values($elements);

    if (count($elements) < $n) {
      throw new \Exception("No visible inputs found");
    }

    $this->clickElement($elements[$number]);
    $elements[$number]->setValue($text);
    $this->waitPageLoaded();
  }

  /**
   * Wait text displayed
   *
   * @Then /^(?:|I )will see "(?P<text>(?:[^"]|\\")*)"$/
   */
  public function waitTextDisplayed($text)
  {
    for ($i = 0; $i <= 11; $i++) {
      if ($i > 10) {
        throw new \Exception("Wait timeout");
      }
      $res = $this->pageContainsText($this->fixStepArgument($text));
      if ($res) {
        return;
      } else {
        usleep(500000);
      }
    }
  }


  private function pageContainsText($text)
  {
    $actual = $this->getSession()->getPage()->getContent();
    $regex  = '/'.preg_quote($text, '/').'/ui';
    return preg_match($regex, $actual);
  }

  private function waitPageLoaded()
  {
    $this->getSession()->wait(10000, "document.readyState === 'complete'");
  }

  private function clickElement($el)
  {
    $this->hoverElement($el);
    $el->click();
    $this->waitPageLoaded();
  }

  private function hoverElement($el)
  {
    $el->mouseOver();
    $this->waitPageLoaded();
  }
}
