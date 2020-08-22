<?php
return [
    'alipay' => [
        'app_id'         => '2021000119663985',
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA2FPph3iNhp/AJJ12T0KTcfnmrh1nY4TZ+9/JbRuNGPz/P61TO0qkjjUT2t/2+YWxaMJRbR9AcKEJHFETumCbFH0f9oS/CeB8ENLlsUOmvLhH+ZxHfB/AAkVQ+8VpW76LzP9hYHkc31aNbfxb4lhocpYrn/M3cVs0nOhOFeQ6qBLPZHf8E/WMt4Zaa3B6rYbgDjm3PsQTWCURFYldYfGN98SIG79jIrH2kvuDUPOTbKly1JCPsmq3VIneuorKsYXdHVq3uLu0BNqMm+80Y+jd7ollWOIz13smeOPWI3C8Wg1OmXq3AgEOp6fhMJVxfX3ImhdXPdJeNgSDNqmDyelHUQIDAQAB',
        'private_key'    => 'MIIEowIBAAKCAQEAnm7hkcnNdtfX5rttWNECB4g9a2sM+6FfFCp9edtaav/fATlqkHSkv6QYOb3FHBpqq+4cfdkMxzMxHe3QS50N2CVbwopAdY6Q9CxzVefO+cMwfVVpBCZ8R8e9ZzfK5nxt0h+3clfQ5EYBwqLdzwRQ3VoT8a/sUlI0W/+wTrHUU5JRy1IlqEejYjbzzAnkVu8EQ6KFsnMJSbrBiLghHHr9NrM8uybwelLBRZVXJ8786WBB4u6s/wLQSz/PZL+cRFefATlKotEdN4dHNLMwMDLI6OG2C1lDTchugei+hQm0HeEZfHe/40p5fArNYAnLeB02JRUzW7y5ecHXiCgaDn+eawIDAQABAoIBAGh4dzeaGWkHVS+pYaZSVANBfDar5Wi79SUoC4th8FJkHNoC0VkmAUj5XJwena41YSe0IId/q2RjRj0VBugFTQ69O6+hWHXsJ3tLIFaCP8IESqIqws/gzMzMUgGREbNAU25eSaoVbAJKg3ijyp1qnCQJ9OLG/Y8e87XCebC6pGbiJi/RhgspfmgnudXYQnHOYxQvLUvZV4s1HlNynQFfiv9E2FrC7tkiS+6zMhZcMNAhZGIROx1TE8AQv+taZT7v6wswW8gVp2hTr9h9V7cnic6LuCKHPBLlJqhJ6hzBne874bDa3Suqqs8XFymfwZu18YY4pZ0vn74v+QNogSjC1wkCgYEA5dUZQCUTCtx5ctbKGkdyMaYHbY4ulrQH0n5m4CgmB8391zARnxzMVezgi7PFUkyALOn9LDLdOuMA8k0xSo6ashxQx5AcgrZC0QoqC7+FCH9TJZiSy3F4pyVH5v0K9IoNAIpZ2Y1CcM2pnhEzwbdhvmXt9RKNdvtW5dlGX3JNXg8CgYEAsHi3B0M+BG2CxkmfTdYpzmmIlFFSDu4gZdQLE68t5n5rpY8XjzLCgrpg0z1n/DrenyHSAsCC3cjBGBTPsocUo+TJkmI2i+6uRExIarxayb3u8QqenuXrN8I7KYmMfBNq+pTiN3RSMqvGEp1K02eOmVcIy6v+SyBCcP8lqRd41eUCgYAnH4NQ2/7F1ooF9nIozwitUunoyE898B90wXeZqLvwkCwpuVEGmMxfxBblMRDh1YvsGVizcWUfZQ0AMgu1+Vh0AUXu8qUnywbMtsI5hbyLmcD5oWM7pnE3Yq0+sMxwnB9ifCqXUeiBc0DwW1VIIINO+eLr2OCj5F0Ce13zWW26GQKBgAnYMh2tmqAUm98D7GcjM2HYcU4U20cJ8bS8h4GpnB3nn4m02dObOU6hpxUhr9NaVWD7OgP9SU+mC1+UiaGj93rNIJGR+QkFX6Nfvgp2R3pJpjK8LO0gVmbd5v8CNVwWmTkxZQ1C5/L7sikHrUzt0f6r5Em4Zo86VXqIQUmVjaAhAoGBAM51wPb2KatKNYnxujCIFtbHXTn2eQkoa8VYSO+3Bk1qDe0V1Izs8ZGPP5/CrIlsSjA4PSkdkLRUFrKT4F6Jn9JLCQ0NaqUQ59KREjjTHIT3lQwraNbtOtJx35Ea4UwWdYj2asRFV+GfBhqOCL9q1BEhtqHgU1Z2qcxWPsPoIH7t',
        'log'            => [
            'file' =>storage_path('logs/alipay.log'),
        ],
    ],

    'wechat' => [
    '   app_id'       => '',
        'mch_id'      => '',
        'key'         => '',
        'cert_client' => '',
        'cert_key'    => '',
        'log'         => [
            'file' => storage_path('logs/wechat_pay.log'),
        ],
    ], 
];