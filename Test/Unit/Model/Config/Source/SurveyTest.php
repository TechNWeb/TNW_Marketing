<?php
/**
 * Created by PhpStorm.
 * User: eermolaev
 * Date: 14.09.18
 * Time: 10:59
 */

namespace TNW\Marketing\Test\Unit\Model\Config\Source;

use TNW\Marketing\Model\Config\Survey;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Area;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\WebsiteRepository;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;


class SurveyTest extends TestCase
{
    /**
     * @var Survey
     */
    private $configSurvey;

    /**
     * @var TypeListInterface|MockObject
     */
    private $cacheTypeList;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|MockObject
     */
    protected $_eventManager;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var TimezoneInterface|MockObject
     */
    private $timezone;

    /**
     * @var array[MockObject]
     */
    private $optionsObjects;

    /**
     * @var ConfigInterface|MockObject
     */
    private $configResource;

    /**
     *
     */
    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);

        /**
         * partial mock, we'll override the "date" method
         */
        $this->timezone = $this->createPartialMock(\Magento\Framework\Stdlib\DateTime\Timezone::class, ['date']);

        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);

        /**
         * prepare survey options
         */
        $this->optionsObjects['tnw_marketing'] = $objectManagerHelper->getObject(\TNW\Marketing\Model\Config\Source\Survey\Options::class);
        $this->optionsObjects['tnw_marketing_extended'] = $objectManagerHelper->getObject(\TNW\Marketing\Model\Config\Source\Survey\ExtendedOptions::class);
        $this->optionsObjects['tnw_marketing_payment'] = $objectManagerHelper->getObject(\TNW\Marketing\Model\Config\Source\Survey\PaymentOptions::class);

        /**
         * Email transport prepare
         */
        $transportBuilder = $this->createMock(TransportBuilder::class);
        $transport = $this->createMock(\Magento\Framework\Mail\TransportInterface::class);

        $transportBuilder
            ->expects($this->any())
            ->method('getTransport')
            ->willReturn($transport);

        $transportBuilder
            ->expects($this->any())
            ->method($this->anything())
            ->willReturnSelf();

        /**
         * store objects prepare
         */
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $store = $this->createMock(Store::class);

        $storeManager
            ->expects($this->any())
            ->method('getStore')
            ->willReturn($store);

        /**
         * create config mock
         */
        $this->configResource = $this->createMock(ConfigInterface::class);

        $this->_eventManager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);

        $this->cacheTypeList = $this->createMock(TypeListInterface::class);

        /**
         * create our object to test
         */
        $this->configSurvey = $objectManagerHelper->getObject(
            Survey::class,
            [
                'scopeConfig' => $this->scopeConfig,
                'timezone' => $this->timezone,
                'optionsObjects' => $this->optionsObjects,
                'transportBuilder' => $transportBuilder,
                'storeManager' => $storeManager,
                'configResource' => $this->configResource,
                '_eventManager' => $this->_eventManager,
                'cacheTypeList' => $this->cacheTypeList
            ]
        );

    }

    /**
     * @dataProvider dataProviderGetSurveyOptionsByType
     */
    public function testGetSurveyOptionsByType($type, $instanceName)
    {
        $option = $this->configSurvey->getSurveyOptionsByType($type);

        $this->assertInstanceOf($instanceName, $option);
    }

    /**
     * @return array
     */
    public function dataProviderGetSurveyOptionsByType()
    {
        return [
            ['tnw_marketing', 'TNW\Marketing\Model\Config\Source\Survey\Options'],
            ['tnw_marketing_extended', 'TNW\Marketing\Model\Config\Source\Survey\ExtendedOptions'],
            ['tnw_marketing_payment', 'TNW\Marketing\Model\Config\Source\Survey\PaymentOptions'],
            [null, 'TNW\Marketing\Model\Config\Source\Survey\Options'],
        ];

    }

    /**
     * @dataProvider dataProviderGetTimestampByRequest
     *
     * @param $params
     * @param $expectation
     */
    public function testGetTimestampByRequest($params, $currentDate, $expectedDate)
    {
        $currentDate = strtotime($currentDate);

        if (!is_null($expectedDate)) {
            $expectedDate = strtotime($expectedDate);
        }

        $this->timezone->expects($this->any())
            ->method('date')
            ->will($this->returnCallback(function ($value = null) use ($currentDate) {

                if (is_null($value)) {
                    /** use defined date as current date */
                    $value = $currentDate;
                }
                if (is_numeric($value)) {
                    /** need it for the timestamp convert correct  */
                    $value = '@' . $value;
                }

                return date_create($value);
            }));

        $this->assertEquals($expectedDate, $this->configSurvey->getTimestampByRequest($params));
    }

    /**
     * @return array
     */
    public function dataProviderGetTimestampByRequest()
    {
        return [
            [ # case 1
                ['snooze_survey' => 1],
                '2018-07-20',
                '2018-07-23'
            ],
            [ # case 2
                ['type' => 'tnw_marketing', 'survey_result' => '0'],
                '2018-07-20',
                null
            ],
            [ # case 2
                ['type' => 'tnw_marketing', 'survey_result' => '1'],
                '2018-07-20',
                '2018-07-27'
            ]
        ];
    }

    /**
     * Just for fun to make sure we can control method calls
     * @throws \ReflectionException
     */
    public function testProcessAnswer()
    {
        /** @var \Magento\User\Model\User|MockObject $user */
        $user = $this->createMock(\Magento\User\Model\User::class);

        $configSurvey = $this->createPartialMock(get_class($this->configSurvey), ['getTimestampByRequest', 'sendEmail', 'setStartDate']);

        $configSurvey
            ->expects($this->once())
            ->method('getTimestampByRequest');

        $configSurvey
            ->expects($this->once())
            ->method('sendEmail');

        $configSurvey
            ->expects($this->once())
            ->method('setStartDate');

        $this->cacheTypeList
            ->expects($this->once())
            ->method('cleanType');

        $this->_eventManager
            ->expects($this->once())
            ->method('dispatch');

        $reflection = new \ReflectionClass($configSurvey);

        $property = $reflection->getProperty('cacheTypeList');
        $property->setAccessible(true);
        $property->setValue($configSurvey, $this->cacheTypeList);

        $property = $reflection->getProperty('_eventManager');
        $property->setAccessible(true);
        $property->setValue($configSurvey, $this->_eventManager);

        $configSurvey->processAnswer(['module' => 'tnw_marketing'], $user);
    }

    /**
     * @dataProvider dataProviderStartDate
     */
    public function testStartDate($module, $path, $date)
    {

        $this->scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->with($path)
            ->willReturn($date);

        self::assertEquals($date, $this->configSurvey->startDate($module));
    }

    /**
     * @return array
     */
    public function dataProviderStartDate()
    {
        return [
            ['tnw_payment', 'tnw_payment/survey/start_date', '1532044800'],
            [null, Survey::DEFAULT_MODULE . '/survey/start_date', '1532044801'],
        ];
    }

    /**
     * @param $module
     * @param $expectation
     *
     * @dataProvider dataProviderGetEmailTemplate
     */
    public function testGetEmailTemplate($module, $expectation)
    {
        self::assertEquals($this->configSurvey->getEmailTemplate($module), $expectation);

    }

    /**
     * @return array
     */
    public function dataProviderGetEmailTemplate()
    {
        return [
            ['tnw_payment', 'tnw_payment_survey_email_template'],
            [null, Survey::DEFAULT_MODULE . '_survey_email_template'],
        ];
    }

    /**
     * @param $params
     * @param $user
     * @param $expectation
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @dataProvider dataProviderSendEmail
     */
    public function testSendEmail($params, $user, $expectation)
    {
        $this->assertEquals($expectation, $this->configSurvey->sendEmail($params, $user));
    }

    /**
     * @return array
     */
    public function dataProviderSendEmail()
    {
        $user = $this->createMock(\Magento\User\Model\User::class);

        return [
            [ # case 1
                [
                    'survey_result' => 1,
                    'type' => 'tnw_marketing',
                    'rating' => 5,
                    'comments' => 'comment text',
                    'module' => 'Module code',
                    'moduleName' => 'TNW Marketing'
                ],
                $user,
                true
            ],
            [ # case 2
                ['snooze_survey' => 1],
                $user,
                false
            ]
        ];
    }

    /**
     * @param $module
     * @param $expectation
     *
     * @dataProvider dataProviderShallShow
     *
     */
    public function testShallShow($startDate, $currentDate, $expectation)
    {
        $this->scopeConfig
            ->expects($this->any())
            ->method('getValue')
            ->willReturn(strtotime($startDate));


        $this->timezone->expects($this->any())
            ->method('date')
            ->will($this->returnCallback(function ($value = null) use ($currentDate) {

                if (is_null($value)) {
                    /** use defined date as current date */
                    $value = $currentDate;
                } else {
                    /** need it for the timestamp convert correct  */
                    $value = '@' . $value;
                }
                return date_create($value);
            }));

        self::assertEquals($expectation, $this->configSurvey->shallShow());
    }

    /**
     * @return array
     */
    public function dataProviderShallShow()
    {
        return [
            ['2018-07-20', '2017-07-20', false],
            ['2018-07-20', '2019-07-20', true]
        ];
    }

    /**
     * @param $module
     * @param $timestamp
     *
     * @dataProvider dataProviderSetStartDate
     *
     */
    public function testSetStartDate($module, $path, $date)
    {

        $this->configResource
            ->expects($this->once())
            ->method('saveConfig')
            ->with($path);

        $this->configSurvey->setStartDate($module, $date);
    }

    /**
     * @return array
     */
    public function dataProviderSetStartDate()
    {
        return [
            ['tnw_payment', 'tnw_payment/survey/start_date', '22222'],
            [null, 'tnw_marketing/survey/start_date', '111111']
        ];
    }
}
