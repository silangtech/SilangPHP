<?php
/**
 * 路由配置
 */

return [
    ['GET','index.test','index/index'],
    ['POST','index.oklala','index/index2'],
    ['GET','abc\d{1}\w{1}','index/index3'],
];