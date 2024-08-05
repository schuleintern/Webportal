<?php



/**
 *
 */
class Widget
{

    private $data = false;

    /**
     * Constructor
     */
    public function __construct($data = false)
    {
        if ($data) {
            $this->data = $data;
        }
        if (file_exists(PATH_LIB . 'models' . DS . 'extensionsModel.class.php')) {
            include_once(PATH_LIB . 'models' . DS . 'extensionsModel.class.php');
        }
    }

    public function getData()
    {
        return $this->data;
    }

    public function render()
    {
        return '';
    }

    public function getScripts()
    {
        return false;
    }

    static public function load($extension, $widget)
    {
        $script = '';
        $html = '';
        if (file_exists(PATH_LIB . 'models' . DS . 'extensionsModel.class.php')) {
            include_once(PATH_LIB . 'models' . DS . 'extensionsModel.class.php');
        }

        $filepath = PATH_EXTENSIONS . $extension . DS . 'widgets' . DS . $widget . DS . 'widget.php';
        if (file_exists($filepath)) {
            include_once $filepath;
            $className = 'ext' . ucfirst($extension) . 'Widget' . ucfirst($widget);


            $class = new $className([
                "path" => PATH_EXTENSIONS . $extension
            ]);

            if ($class && method_exists($class, 'render')) {
                $html = $class->render(true);
                if (method_exists($class, 'getScriptData')) {
                    $varname = 'globals_widget_' . $extension . '_' . $widget;
                    $script .= AbstractPage::getScriptData($class->getScriptData(), $varname);
                }
                if (method_exists($class, 'getScripts')) {
                    $script .= FILE::getScripts($class->getScripts());
                }
            }

        }
        return [
            "html" => $html,
            "script" => $script
        ];

    }
}