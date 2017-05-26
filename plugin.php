<?php

    use Symfony\Component\Yaml\Yaml;

    class DropdownPlugin extends AdministroPlugin {

        var $dataFile, $files, $dropdowns;

        public function configLoaded() {
            // Set file locations
            $this->dataFile = $this->administro->rootDir . 'data/dropdown/data.yaml';
            $this->files = $this->administro->rootDir . 'data/dropdown/files/';
            // Create directory
            @mkdir($this->files, 0777, true);
            // Add dropdown file viewer
            $this->administro->reservedRoutes['viewdropdown'] = 'plugins/Dropdown/viewdropdownroute.php';
            // Add admin page
            $this->administro->adminPages['dropdown'] =
                array('icon' => 'caret-square-o-down', 'name' => 'Dropdown', 'file' => 'plugins/Dropdown/admin/dropdown.php');
            // Add forms
            array_push($this->administro->forms, 'adddropdown', 'adddropdownfile', 'sortdropdown');
        }

        public function onLoadingPages() {
            $this->load();
            foreach($this->dropdowns as $id => $dd) {
                $v = '<form action="'.$this->administro->baseDir.'viewdropdown" method="get"><select name="file">';
                foreach($dd['files'] as $name => $file) {
                    $v .= '<option value="'.$file.'">'.$name.'</option>';
                }
                $v .= '</select><input type="hidden" name="dropdown" value="'.$id.'"><input type="submit" value="View"></form>';
                $this->administro->variables[$dd['name']] = $v;
            }
        }

        public function load() {
            // Make sure file exists
            if(!file_exists($this->dataFile)) {
                file_put_contents($this->dataFile, Yaml::dump(array()));
            }
            // Load events
            $this->dropdowns = Yaml::parse(file_get_contents($this->dataFile));
        }

    }

    function adddropdownform($administro) {
        $params = $administro->verifyParameters('adddropdown', array('name', 'permission'));
        if($params !== false) {
            if($administro->hasPermission('admin.dropdown')) {
                $plugin = $administro->plugins['Dropdown'];
                $plugin->load();
                $dropdowns = $plugin->dropdowns;
                // Verify dropdown does not exist
                $id = strtolower(str_replace(' ', '', $params['name']));
                if(!isset($dropdowns[$id])) {
                    // Save dropdown
                    $dropdowns[$id] = array(
                        'name' => $params['name'],
                        'permission' => $params['permission'],
                        'files' => array()
                    );
                    file_put_contents($plugin->dataFile, Yaml::dump($dropdowns));
                    mkdir($plugin->files . '/' . $id);
                    $administro->redirect('admin/dropdown', 'good/Created dropdown!');
                } else {
                    $administro->redirect('admin/dropdown', 'bad/A dropdown with that name already exists!');
                }
            } else {
                $administro->redirect('admin/dropdown', 'bad/Invalid permission!');
            }
        } else {
            $administro->redirect('admin/dropdown', 'bad/Invalid parameters!');
        }
    }

    function adddropdownfileform($administro) {
        $params = $administro->verifyParameters('adddropdownfile', array('dropdown', 'name'));
        if($params !== false && isset($_FILES['file'])) {
            if($administro->hasPermission('admin.dropdown')) {
                $plugin = $administro->plugins['Dropdown'];
                $plugin->load();
                $dropdowns = $plugin->dropdowns;
                $id = $params['dropdown'];
                // Verify dropdown exists
                if(isset($dropdowns[$id])) {
                    $dir = $plugin->files . '/' . $id . '/';
                    // Set new dropdown data
                    $dropdown = $dropdowns[$id];
                    $files = $dropdown['files'];
                    $files[$params['name']] = $_FILES['file']['name'];
                    $dropdown['files'] = $files;
                    $dropdowns[$id] = $dropdown;
                    // Save the file
                    if ($_FILES['file']['size'] > 10000000) {
                        $administro->redirect('admin/dropdown', 'bad/File must be under 10MB!');
                    }
                    if(file_exists($dir . $_FILES['file']['name'])) {
                        $administro->redirect('admin/dropdown', 'bad/File already exists!');
                    }
                    move_uploaded_file($_FILES['file']['tmp_name'], $dir . $_FILES['file']['name']);
                    // Complete save process
                    file_put_contents($plugin->dataFile, Yaml::dump($dropdowns));
                    $administro->redirect('admin/dropdown', 'good/Added item!');
                } else {
                    $administro->redirect('admin/dropdown', 'bad/Dropdown does not exist!');
                }
            } else {
                $administro->redirect('admin/dropdown', 'bad/Invalid permission!');
            }
        } else {
            $administro->redirect('admin/dropdown', 'bad/Invalid parameters!');
        }
    }

    function sortdropdownform($administro) {
        $params = $administro->verifyParameters('sortdropdown', array('dropdown', 'data'), false);
        if($params !== false) {
            if($administro->hasPermission('admin.dropdown')) {
                $plugin = $administro->plugins['Dropdown'];
                $plugin->load();
                $dropdowns = $plugin->dropdowns;
                $id = $params['dropdown'];
                // Verify dropdown exists
                if(!isset($dropdowns[$id])) {
                    die('Invalid dropdown!');
                }
                $currentFiles = $dropdowns[$id]['files'];
                $newFiles = array();
                foreach(explode(',', $params['data']) as $fName) {
                    if(isset($currentFiles[$fName])) {
                        $newFiles[$fName] = $currentFiles[$fName];
                    }
                }
                $dropdowns[$id]['files'] = $newFiles;
                file_put_contents($plugin->dataFile, Yaml::dump($dropdowns));
                die('Saved!');
            } else {
                die('Invalid permission!');
            }
        } else {
            die('Invalid parameters!');
        }
    }
