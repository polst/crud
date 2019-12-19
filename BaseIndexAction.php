<?php
/**
 * @author Basic App Dev Team
 * @license MIT
 * @link http://basic-app.com
 */
namespace BasicApp\Crud;

abstract class BaseIndexAction extends Action
{

    const EVENT_BEFORE_FIND = 'onBeforeFind';

    protected $orderBy;

    protected $perPage = 25;

    protected $onBeforeFind = [];

    public function run(array $options = [])
    {
        $model = $this->createModel();

        $query = $model->builder();

        $searchModel = $this->createSearchModel();

        if ($searchModel)
        {
            $search = $searchModel->createEntity();

            $search->fill($this->request->getGet());

            if ($searchModel->validate($search))
            {
                $search->applyToQuery($query);
            }
            else
            {
                $query->where('1=0');
            }
        }
        else
        {
            $search = null;
        }

        $parentKey = $this->parentKey;

        if ($parentKey)
        {
            $parentId = $this->request->getGet($this->parentKey, null);

            if ($parentId)
            {
                $query->where($parentKey, $parentId);
            }
        }
        else
        {
            $parentId = null;
        }

        if ($this->orderBy)
        {
            $query->orderBy($this->orderBy);
        }

        $this->trigger(static::EVENT_BEFORE_FIND, ['query' => $query]);

        $perPage = $this->perPage;

        if ($perPage)
        {
            $elements = $query->paginate($perPage);
        }
        else
        {
            $elements = $query->findAll();
        }

        return $this->render($this->view, [
            'model' => $model,
            'elements' => $elements,
            'pager' => $query->pager,
            'parentId' => $parentId,
            'parentKey' => $parentKey,
            'searchModel' => $searchModel,
            'search' => $search,
            'searchErrors' => $searchModel ? (array) $searchModel->errors() : []
        ]);
    }

}