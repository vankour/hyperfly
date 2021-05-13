<?php

declare (strict_types=1);
namespace Api\ApiEntrance\Admin\v1\Controller;

use Api\ApiBase\BaseController;
use Api\ApiEntrance\Admin\Model\AdminModel;
use Api\ApiEntrance\Admin\Model\RoleAdminModel;
use Api\ApiEntrance\Admin\Model\RoleModel;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\Di\Annotation\Inject;
/**
 * 角色管理员关联控制器
 * Class RoleAdminController
 * @package Api\ApiEntrance\Admin\v1\Controller
 * @Controller(prefix="Admin/v1/RoleAdmin")
 */
class RoleAdminController extends BaseController
{
    use \Hyperf\Di\Aop\ProxyTrait;
    use \Hyperf\Di\Aop\PropertyHandlerTrait;
    function __construct()
    {
        if (method_exists(parent::class, '__construct')) {
            parent::__construct(...func_get_args());
        }
        $this->__handlePropertyHandler(__CLASS__);
    }
    /**
     * @Inject
     * @var RoleAdminModel
     */
    private $roleAdminModel;
    /**
     * @Inject
     * @var RoleModel
     */
    private $roleModel;
    /**
     * @Inject
     * @var AdminModel
     */
    private $adminModel;
    /**
     * 删除分配给管理员的角色
     * @RequestMapping(path="deleteAdminRole", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function deleteAdminRole()
    {
        $rules = ['adminId' => ['required', 'exists:' . $this->adminModel->getTable() . ',id'], 'roleIds' => ['required']];
        $message = [
            //adminId
            'adminId.required' => '参数:attribute不存在',
            'adminId.exists' => '参数:attribute不存在',
            //roleIds
            'roleIds.required' => '参数:attribute不存在',
        ];
        $validate = $this->validator($this->request->all(), $rules, $message);
        if ($validate->fails()) {
            return $this->fail($validate->errors()->first());
        }
        $requestData = $this->request->all();
        $adminId = (int) $requestData['adminId'];
        $roleIds = json_decode($requestData['roleIds'], true);
        if (!is_array($roleIds)) {
            return $this->fail('参数:roleIds不是合法的json对象格式');
        }
        return $this->roleAdminModel->deleteAdminRole($adminId, $roleIds) ? $this->success() : $this->fail($this->roleAdminModel->getModelError());
    }
    /**
     * 分配角色
     * @RequestMapping(path="assignAdminRole", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function assignAdminRole()
    {
        $rules = ['adminId' => ['required', 'exists:' . $this->adminModel->getTable() . ',id'], 'roleIds' => ['required']];
        $message = [
            //adminId
            'adminId.required' => '参数:attribute不存在',
            'adminId.exists' => '参数:attribute的值不存在',
            //roleIds
            'roleIds.required' => '参数:attribute不存在',
        ];
        $validate = $this->validator($this->request->all(), $rules, $message);
        if ($validate->fails()) {
            return $this->fail($validate->errors()->first());
        }
        $requestData = $this->request->all();
        $adminId = (int) $requestData['adminId'];
        $roleIds = json_decode($requestData['roleIds'], true);
        if (!is_array($roleIds)) {
            return $this->fail('参数roleIds不是合法的json对象格式');
        }
        return $this->roleAdminModel->assignAdminRole($adminId, $roleIds) ? $this->success() : $this->fail($this->roleAdminModel->getModelError());
    }
    /**
     * 获取所有角色/未分配的角色
     * @RequestMapping(path="getAdminRole", method="post")
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function getAdminRole()
    {
        $adminId = (int) $this->request->input('adminId');
        if ($adminId === null) {
            return $this->fail('参数adminId不存在');
        }
        $allRole = $this->roleModel->getAllRole(['id', 'name']);
        $adminRole = $this->roleAdminModel->getAdminRole($adminId);
        $returnData = ['adminRole' => $adminRole, 'noAssignRole' => $this->getNoAssignRole($allRole, $adminRole)];
        return $this->success($returnData);
    }
    /**
     * 获取未分配的角色
     * @param array $allRole
     * @param array $adminRole
     * @return array
     */
    private function getNoAssignRole(array $allRole, array $adminRole)
    {
        $allRoleArr = [];
        $adminRoleArr = [];
        foreach ($allRole as $k => $v) {
            $allRoleArr[$v['id']] = $v;
        }
        foreach ($adminRole as $k => $v) {
            $adminRoleArr[$v['role_id']] = $v;
        }
        $diff = array_diff_key($allRoleArr, $adminRoleArr);
        $noAssignRole = [];
        foreach ($diff as $k => $v) {
            $noAssignRole[] = $v;
        }
        return $noAssignRole;
    }
}