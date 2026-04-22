##### SHOPIFY AUTOMATION

# Channable Free Gift

# Automation

### How to detect Channable orders and automatically add a free gift line item using

### a Custom Shopify App hosted on Vercel

##### Technical Solution Guide · April 2026 · Custom App + Vercel + Shopify GraphQL API

## 1. The Requirement

##### When a Shopify order is created, the system must automatically detect whether the order originated from

##### Channable. If confirmed, a free gift product must be appended as an additional line item at £0.00 —

##### without notifying the customer.

##### Step 1 Step 2 Step 3 Step 4

##### Order Created

##### (Shopify webhook)

##### Detect Channable

##### source

##### Call Order

##### Editing API

##### Free gift added

##### to order 3

## 2. Solution Comparison

```
Solution Complexity Hosting Add Line Item Verdict
```

Custom Shopify App Medium Vercel / any 3 Full support n Recommended

```
Shopify Flow Low None (native) 7 Not native n Needs bridge
```

```
n8n Low n8n Cloud 3 via API 3 Good for speed
```

##### n Shopify Flow cannot add line items to existing orders natively. It would still require an external webhook/API

##### bridge — making a custom app the cleaner end-to-end solution.

## 3. Hosting on Vercel

##### Vercel is an excellent fit for this use case. Your webhook handler is a short-lived, stateless, event-driven

##### function — exactly what Vercel Serverless Functions are optimised for.

##### 3 Works well on Vercel n Critical caveat: Timeouts

- Auto-scaling, zero maintenance
- Git-based deploys (push to deploy)
- Free SSL out of the box
- Environment variable management
- Serverless = pay per invocation

##### Shopify expects a 200 response within 5

##### seconds or it retries.

##### Vercel Hobby: execution stops after response.

##### Vercel Pro: background functions continue.

##### Solution: Use Vercel Pro or add a queue

##### (Upstash QStash).

## 4. Recommended Architecture

### Option A — Vercel Pro (Simplest)

```
Shopify Webhook (orders/create)
nnn Vercel Serverless Function
n 1. Verify HMAC signature
n 2. res.status(200).end() ← acknowledge Shopify immediately
n 3. Check Channable source ← continues running (Vercel Pro)
nnn Shopify GraphQL Order Editing API
orderEditBegin → orderEditAddVariant → orderEditCommit
```

### Option B — Vercel Free + Queue (Robust)

```
Shopify Webhook
nnn Vercel Function nnn Upstash QStash (queue)
nnn Second Vercel Function
nnn Shopify Order Editing API
```

##### n Upstash QStash is free-tier friendly, has built-in retries, and pairs seamlessly with Vercel. Ideal if you want

##### zero infrastructure cost.

## 5. Detecting a Channable Order

##### Channable typically marks orders in one or more of the following ways. Check all three to maximise

##### coverage:

Detection Method Shopify Field Example Value

Source name order.source_name 'channable'

Order tag order.tags 'channable', 'channel-channable'

Note attribute order.note_attributes[] { name: 'source', value: 'channable' }

## 6. Full Implementation

### api/webhook.js — Vercel Serverless Function

```
import crypto from 'crypto';
```

```
export default async function handler(req, res) {
// 1. Verify the request is genuinely from Shopify
const hmac = req.headers['x-shopify-hmac-sha256'];
const digest = crypto
.createHmac('sha256', process.env.SHOPIFY_WEBHOOK_SECRET)
.update(req.body, 'utf8')
.digest('base64');
```

```
if (digest !== hmac) return res.status(401).end();
```

```
// 2. Acknowledge Shopify immediately (must be < 5 seconds)
res.status(200).end();
```

```
// 3. Parse order and check Channable source
const order = JSON.parse(req.body);
const isChannable =
order.source_name === 'channable' ||
order.tags?.toLowerCase().includes('channable') ||
order.note_attributes?.some(
a => a.name === 'source' && a.value === 'channable'
);
```

```
if (!isChannable) return;
```

```
// 4. Add free gift via Shopify Order Editing API
await addFreeGift(order.admin_graphql_api_id);
}
```

### addFreeGift() — GraphQL Order Editing Flow

```
async function addFreeGift(orderGid) {
const GIFT_VARIANT_GID = process.env.GIFT_VARIANT_GID;
// e.g. 'gid://shopify/ProductVariant/12345678'
```

```
// Step 1: Begin edit session
const begin = await gql(`
mutation { orderEditBegin(id: "${orderGid}") {
calculatedOrder { id }
}}
`);
const calcId = begin.data.orderEditBegin.calculatedOrder.id;
```

```
// Step 2: Add free gift line item at £0.
await gql(`
mutation {
orderEditAddVariant(
id: "${calcId}"
variantId: "${GIFT_VARIANT_GID}"
quantity: 1
allowDuplicates: false
) { calculatedLineItem { id } }
}
`);
```

```
// Step 3: Commit — no customer notification
await gql(`
mutation {
orderEditCommit(
id: "${calcId}"
notifyCustomer: false
staffNote: "Free gift added — Channable order"
) { order { id } }
}
`);
}
```

### gql() — Shopify GraphQL Helper

```
async function gql(query) {
const res = await fetch(
`https://${process.env.SHOPIFY_STORE}.myshopify.com/admin/api/2024-01/graphql.json`,
{
method: 'POST',
headers: {
'Content-Type': 'application/json',
'X-Shopify-Access-Token': process.env.SHOPIFY_ACCESS_TOKEN,
},
body: JSON.stringify({ query }),
}
);
return res.json();
}
```

## 7. Environment Variables

Variable Description Where to find

SHOPIFY_WEBHOOK_SECRET HMAC secret for verifying webhooks Shopify Admin → Notifications → Webhooks

SHOPIFY_ACCESS_TOKEN Admin API access token Shopify Partners → App → API credentials

SHOPIFY_STORE Your store handle e.g. my-store (from my-store.myshopify.com)

GIFT_VARIANT_GID GraphQL GID of the gift variant Shopify Admin → Products → variant URL

## 8. Deployment Checklist

##### n Create a new Shopify Custom App in Partners dashboard

##### n Grant scopes: write_orders, read_orders

##### n Deploy Vercel project and set all 4 environment variables

##### n Register webhook: Shopify Admin → Settings → Notifications → orders/create

##### n Set webhook URL to: https://your-app.vercel.app/api/webhook

##### n Note down the Webhook Signing Secret and add to SHOPIFY_WEBHOOK_SECRET

##### n Find your free gift product variant GID and add to GIFT_VARIANT_GID

##### n Place a test order tagged 'channable' and verify gift is added

##### n Monitor Vercel function logs for first 24 hours

## 9. Summary & Recommendation

#### Recommended Stack

```
Component Choice Reason
```

##### App type Custom Shopify App Full control, production-grade

##### Hosting Vercel Pro Background function support

##### Language Node.js Best Shopify ecosystem support

##### API Shopify GraphQL Admin API Required for Order Editing

##### Estimated build time ~4–6 hours ~100 lines of code total

##### n The entire solution is ~100 lines of JavaScript. It is stateless, scalable, and requires no database. Vercel

##### handles all infrastructure automatically.
