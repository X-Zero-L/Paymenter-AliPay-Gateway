# AliPay Payment Gateway Extension

A payment gateway extension for [Paymenter](https://github.com/Paymenter/Paymenter) that integrates with Alipay domestic payment services.

![Version](https://img.shields.io/badge/version-1.0.0-blue)

[中文版本](./README.md)

## Features

- Support for Alipay PC web payment
- Support for sandbox and production environment switching
- Support for synchronous return and asynchronous notification
- Complete order information and product details transmission
- Support for Alipay signature verification and encryption

## Requirements

- [Paymenter](https://github.com/Paymenter/Paymenter) installed
- PHP 8.1 or higher
- alipaysdk/openapi extension
- Alipay merchant account and keys, or sandbox account

## Installation

### Standard Installation

1. Add the dependency to your project's composer.json file:

```json
"require": {
    "alipaysdk/openapi": "*@dev"
}
```

2. Run the Composer update command to install dependencies:

```bash
composer update --no-dev --optimize-autoloader
```

3. Download this extension
4. Copy the entire AliPay folder to Paymenter's `app/Extensions/Gateways` directory
5. Add the new payment gateway in the Paymenter admin panel

### Docker Installation

If you are using Docker to deploy Paymenter, you can directly clone this project and refer to the docker-compose.yml and Dockerfile for configuration.

## Configuration

After adding the payment gateway in the Paymenter admin panel, you need to configure the following parameters:

| Parameter         | Description                                   |
| ----------------- | --------------------------------------------- |
| APP ID            | Alipay application App ID                     |
| Private Key       | Application private key for signing           |
| AliPay Public Key | Alipay public key for signature verification  |
| Encrypt Key       | Data encryption key (optional)                |
| Live              | Switch between sandbox/production environment |

## Obtaining Keys

1. Log in to the [Alipay Open Platform](https://open.alipay.com/)
2. Create an application and obtain the APP ID
3. Generate RSA key pair according to platform guidelines
4. Upload the generated public key to the Alipay Open Platform
5. Obtain the Alipay public key

## Testing

1. Set the extension to sandbox environment
2. Get test accounts from [Alipay Sandbox Environment](https://openhome.alipay.com/platform/appDaily.htm)
3. Create test orders for payment testing

## Asynchronous Notification Setup

The application gateway address set in the Alipay Open Platform should point to your system's asynchronous notification address. By default, you should set it to:

```
https://your-domain/extensions/alipay/webhook
```

Ensure that this address is accessible from the internet to receive notifications from Alipay.

## Web Callback Setup

The authorization callback address set in the Alipay Open Platform should point to your domain. By default, you should set it to:

```
https://your-domain
```

## Frequently Asked Questions

**Q: Problems when installing dependencies**  
A: Make sure your composer.json file is correctly configured with `"alipaysdk/openapi": "*@dev"` and that you have run the `composer update` command.

**Q: Cannot find Alipay extension when deployed with Docker**  
A: Check if the volume mapping is correct and ensure that the AliPay directory files are properly placed. You can check inside the container:

```bash
docker compose exec paymenter ls -la /var/www/paymenter/app/Extensions/Gateways/AliPay
```

**Q: "System Exception" displayed after payment page redirect**  
A: Please check if your APP ID and key configuration are correct, and if your application has been approved.

**Q: Payment successful but order status not updated**  
A: Please check if the asynchronous notification address is correctly configured and if your server can receive callback notifications from Alipay.

**Q: Cannot make payments in sandbox environment**  
A: Confirm that you have correctly set up the sandbox environment and are using the sandbox accounts provided by the Alipay Developer Center for testing.

**Q: Gateway configuration not appearing in Paymenter admin panel**  
A: Restart the Paymenter application or clear the cache to ensure the extension is properly loaded.

## Security Notes

- Ensure your private key is secure and not exposed in public places or source code
- In production environments, it is recommended to enable data encryption for increased security
- Regularly update the Alipay SDK and this extension to get the latest security fixes

## Contributing

Feel free to submit issues and contribute to the code. If you have any questions or suggestions, please submit an issue or PR on GitHub.

## License

This project follows the MIT license.

## Changelog

- 1.0.0 (2025-03-04): Initial version released, supporting basic PC web payment functionality, signature verification, and logging
