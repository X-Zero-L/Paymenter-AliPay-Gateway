# 支付宝支付网关扩展 (AliPay Payment Gateway)

适用于 [Paymenter](https://github.com/Paymenter/Paymenter) 的支付宝国内支付网关扩展。

![版本](https://img.shields.io/badge/版本-1.0.0-blue)

[English Version](./README.en.md)

## 功能特性

- 支持支付宝 PC 网页支付
- 支持沙箱环境和正式环境切换
- 支持同步返回和异步通知
- 完整的订单信息和商品明细传输
- 支持支付宝签名验证和加密

## 安装要求

- [Paymenter](https://github.com/Paymenter/Paymenter) 已安装
- PHP 8.1 或更高版本
- alipaysdk/openapi 扩展
- 支付宝商户账号及密钥，或沙箱账号

## 安装步骤

### 标准安装

1. 在项目的 composer.json 文件中添加依赖项：

```json
"require": {
    "alipaysdk/openapi": "*@dev"
}
```

2. 运行 Composer 更新命令安装依赖：

```bash
composer update --no-dev --optimize-autoloader
```

3. 下载本扩展
4. 将整个 AliPay 文件夹复制到 Paymenter 的 `app/Extensions/Gateways` 目录下
5. 在 Paymenter 管理后台添加新的支付网关

### Docker 安装

如果您使用 Docker 部署 Paymenter，可以直接 clone 本项目，参考本项目的 docker-compose.yml 和 Dockerfile 文件进行配置。

## 配置说明

在 Paymenter 后台添加支付网关后，需要配置以下参数：

| 参数              | 说明                     |
| ----------------- | ------------------------ |
| APP ID            | 支付宝应用的 App ID      |
| Private Key       | 应用私钥，用于签名       |
| AliPay Public Key | 支付宝公钥，用于验证签名 |
| Encrypt Key       | 数据加密密钥 (可选)      |
| Live              | 切换沙箱/正式环境        |

## 获取密钥

1. 登录[支付宝开放平台](https://open.alipay.com/)
2. 创建应用并获取 APP ID
3. 按照平台指引生成 RSA 密钥对
4. 将生成的公钥上传至支付宝开放平台
5. 获取支付宝公钥

## 测试

1. 设置扩展为沙箱环境
2. 在 [支付宝沙箱环境](https://openhome.alipay.com/platform/appDaily.htm) 获取测试账号
3. 创建测试订单进行支付测试

## 异步通知设置

在支付宝开放平台设置的应用网关地址应当指向您系统中的异步通知地址。默认情况下，您应当设置为：

```
https://您的域名/extensions/alipay/webhook
```

确保该地址可以从互联网访问，以便接收支付宝的通知。

## 网页回调设置

在支付宝开放平台设置的授权回调地址应当指向您的域名。默认情况下，您应当设置为：

```
https://您的域名
```

## 常见问题

**Q: 安装依赖时出现问题**  
A: 确保您的 composer.json 文件正确配置了 `"alipaysdk/openapi": "*@dev"`，并且运行了 `composer update` 命令。

**Q: Docker 部署时无法找到支付宝扩展**  
A: 检查卷映射配置是否正确，并确保 AliPay 目录中的文件已正确放置。可以进入容器检查：

```bash
docker compose exec paymenter ls -la /var/www/paymenter/app/Extensions/Gateways/AliPay
```

**Q: 支付页面跳转后显示"系统异常"**  
A: 请检查您的 APP ID 和密钥配置是否正确，以及您的应用是否已通过审核。

**Q: 支付成功但订单状态未更新**  
A: 请检查异步通知地址配置是否正确，以及服务器是否能接收到支付宝的回调通知。

**Q: 沙箱环境中无法支付**  
A: 确认您已正确设置沙箱环境，并使用支付宝开发者中心提供的沙箱账号进行测试。

**Q: 网关配置不显示在 Paymenter 后台**  
A: 重启 Paymenter 应用或清除缓存，确保扩展被正确加载。

## 安全说明

- 请确保您的私钥安全，不要在公共场合或源代码中暴露
- 生产环境中，建议启用数据加密功能以提高安全性
- 定期更新支付宝 SDK 和本扩展，以获取最新的安全修复

## 贡献

欢迎提交问题和贡献代码，如有任何问题或建议，请在 GitHub 上提交 issue 或 PR。

## 许可

本项目遵循 MIT 许可协议。

## 更新日志

- 1.0.0 (2025-03-04): 初始版本发布，支持基本电脑端网页支付功能、签名验证和日志记录
