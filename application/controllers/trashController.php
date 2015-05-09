<?php
class trashController extends Controller
{
    public function get()
    {
        $webpage = new webpageModel;
        $weblink = new weblinkModel;
        $webtd   = new webtdModel;

        $trash = array_merge($webpage->getTrashed(), $weblink->getTrashed(), $webtd->getTrashed());

        usort($trash, function ($a, $b) {
            return $a['trash'] == $b['trash'] ? 0 : ($a['trash'] < $b['trash'] ? 1 : -1);
        });

        return array(
            'trash' => $trash
        );
    }

    public function untrash($remove = false)
    {
        if ($remove && $this->id == 'all') {
            $model = new weblinkModel;
            $model->remove_hard(true);

            $model = new webtdModel;
            $model->remove_hard(true);

            $model = new webpageModel;
            $model->remove_hard(true);
        } else {
            list ($modelName, $id) = explode(':', $this->id);
            $modelClass = $modelName . 'Model';

            if (class_exists($modelClass)) {
                /**
                 * @var webModel $model
                 */
                $model = new $modelClass($id);

                if ($remove)
                    $model->remove_hard();
                else
                    $model->untrash();
            }
        }

        $this->redirect('trash/get');
    }

    public function remove()
    {
        $this->untrash(true);
    }
}
