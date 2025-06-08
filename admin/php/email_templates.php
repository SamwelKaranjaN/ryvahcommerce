<?php
// Email templates for marketing campaigns

class EmailTemplates
{

    public static function getTemplates()
    {
        return [
            'welcome' => [
                'name' => 'Welcome Email',
                'subject' => 'Welcome to Ryvah Commerce, {name}!',
                'body' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f9fa;"><div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;"><h1 style="margin: 0; font-size: 28px;">Welcome to Ryvah Commerce!</h1></div><div style="background: white; padding: 30px; border-radius: 0 0 10px 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);"><p style="font-size: 18px; color: #2c3e50; margin-bottom: 20px;">Dear {name},</p><p style="color: #6c757d; line-height: 1.6; margin-bottom: 20px;">Thank you for joining Ryvah Commerce! We are excited to have you as part of our community.</p><p style="color: #6c757d; line-height: 1.6; margin-bottom: 20px;">Explore our wide range of products including books, ebooks, and premium paints. We are committed to providing you with the best shopping experience.</p><div style="text-align: center; margin: 30px 0;"><a href="https://ryvahcommerce.com" style="background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: bold; display: inline-block;">Start Shopping</a></div><p style="color: #6c757d; line-height: 1.6;">Best regards,<br>The Ryvah Commerce Team</p></div></div>'
            ],

            'promotion' => [
                'name' => 'Special Promotion',
                'subject' => 'Exclusive 25% OFF for You, {name}!',
                'body' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f9fa;"><div style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;"><h1 style="margin: 0; font-size: 32px;">🎉 SPECIAL OFFER 🎉</h1><h2 style="margin: 10px 0 0 0; font-size: 24px;">25% OFF Everything!</h2></div><div style="background: white; padding: 30px; border-radius: 0 0 10px 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);"><p style="font-size: 18px; color: #2c3e50; margin-bottom: 20px;">Hi {name},</p><p style="color: #6c757d; line-height: 1.6; margin-bottom: 20px;">We have an exclusive offer just for you! Get <strong>25% OFF</strong> on all products in our store.</p><div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; border-radius: 8px; text-align: center; margin: 25px 0;"><p style="margin: 0; font-size: 20px; color: #856404; font-weight: bold;">Use Code: <span style="background: #f39c12; color: white; padding: 8px 15px; border-radius: 5px;">SAVE25</span></p></div><div style="text-align: center; margin: 30px 0;"><a href="https://ryvahcommerce.com" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: bold; display: inline-block; font-size: 18px;">Shop Now & Save!</a></div></div></div>'
            ],

            'newsletter' => [
                'name' => 'Monthly Newsletter',
                'subject' => 'Ryvah Commerce Monthly Update - {name}',
                'body' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f9fa;"><div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;"><h1 style="margin: 0; font-size: 28px;">Monthly Newsletter</h1><p style="margin: 10px 0 0 0; opacity: 0.9;">Stay updated with our latest products and offers</p></div><div style="background: white; padding: 30px; border-radius: 0 0 10px 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);"><p style="font-size: 18px; color: #2c3e50; margin-bottom: 20px;">Hello {name},</p><p style="color: #6c757d; line-height: 1.6; margin-bottom: 25px;">Here is what is new at Ryvah Commerce this month:</p><div style="border-left: 4px solid #667eea; padding-left: 20px; margin: 25px 0;"><h3 style="color: #2c3e50; margin: 0 0 10px 0;">📚 New Book Arrivals</h3><p style="color: #6c757d; line-height: 1.6; margin: 0;">Discover our latest collection of books across various genres.</p></div><div style="text-align: center; margin: 30px 0;"><a href="https://ryvahcommerce.com" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: bold; display: inline-block;">Explore New Products</a></div></div></div>'
            ],

            'abandoned_cart' => [
                'name' => 'Abandoned Cart Reminder',
                'subject' => 'Don\'t forget your items, {name}!',
                'body' => '
                    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f9fa;">
                        <div style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
                            <h1 style="margin: 0; font-size: 28px;">🛒 Items Waiting for You!</h1>
                        </div>
                        <div style="background: white; padding: 30px; border-radius: 0 0 10px 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                            <p style="font-size: 18px; color: #2c3e50; margin-bottom: 20px;">Hi {name},</p>
                            <p style="color: #6c757d; line-height: 1.6; margin-bottom: 20px;">
                                You left some great items in your cart! Don\'t let them get away.
                            </p>
                            <p style="color: #6c757d; line-height: 1.6; margin-bottom: 25px;">
                                Complete your purchase now and enjoy fast, secure checkout.
                            </p>
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="https://ryvahcommerce.com/cart" style="background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: bold; display: inline-block; font-size: 18px;">
                                    Complete Your Purchase
                                </a>
                            </div>
                            <p style="color: #adb5bd; font-size: 14px; text-align: center;">
                                Need help? Contact our support team anytime.
                            </p>
                        </div>
                    </div>
                '
            ]
        ];
    }

    public static function getTemplate($templateId)
    {
        $templates = self::getTemplates();
        return $templates[$templateId] ?? null;
    }

    public static function getTemplatesList()
    {
        $templates = self::getTemplates();
        $list = [];
        foreach ($templates as $id => $template) {
            $list[$id] = $template['name'];
        }
        return $list;
    }
}
