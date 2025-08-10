-- Replace created_by if needed (default 1 = admin)

-- 1) Social Content Studio
INSERT INTO dashboards (name, slug, description, created_by, settings_json) VALUES
('Social Content Studio', 'social-content-studio', 'Create blogs, captions, and schedule posts; plus live metrics.', 1, JSON_OBJECT('theme','blue'));
SET @dash1 = LAST_INSERT_ID();

INSERT INTO widgets(dashboard_id,type,title,position_x,position_y,width,height,include_in_main,autorun_on_load,config_json,style_json) VALUES
(@dash1,'app','Blog Generator',0,0,4,3,0,0,
  JSON_OBJECT('webhook_url','https://YOUR_N8N/webhook/blog_gen',
              'params_schema', JSON_ARRAY(
                JSON_OBJECT('key','topic','label','Blog Topic','type','text','required',true),
                JSON_OBJECT('key','tone','label','Tone','type','select','options',JSON_ARRAY('Friendly','Professional','Playful'))
              ),
              'output_schema', JSON_OBJECT('type','rich', 'fields', JSON_ARRAY('title','summary','body','files'))
  ),
  JSON_OBJECT('icon','file-text','accent','#0d6efd')
),
(@dash1,'app','Instagram Caption Maker',4,0,4,3,0,0,
  JSON_OBJECT('webhook_url','https://YOUR_N8N/webhook/ig_caption',
              'params_schema', JSON_ARRAY(
                JSON_OBJECT('key','product','label','Product','type','text','required',true),
                JSON_OBJECT('key','style','label','Style','type','select','options',JSON_ARRAY('Hype','Educational','Story'))
              ),
              'output_schema', JSON_OBJECT('type','text')
  ),
  JSON_OBJECT('icon','hash','accent','#6f42c1')
),
(@dash1,'data','YouTube Subscribers',8,0,2,2,1,1,
  JSON_OBJECT('webhook_url','https://YOUR_N8N/webhook/yt_subs',
              'output_schema', JSON_OBJECT('type','kpi'))
  ,JSON_OBJECT('icon','bar-chart-2','accent','#198754')
),
(@dash1,'data','Latest Headlines',8,2,4,2,1,1,
  JSON_OBJECT('webhook_url','https://YOUR_N8N/webhook/news',
              'output_schema', JSON_OBJECT('type','list'))
  ,JSON_OBJECT('icon','rss','accent','#20c997'));

-- 2) Executive Metrics Wall
INSERT INTO dashboards (name, slug, description, created_by, settings_json) VALUES
('Executive Metrics Wall', 'exec-metrics-wall', 'Company KPIs: revenue, pipeline, web traffic, CSAT. Autoruns and refreshes with Main.', 1, JSON_OBJECT('theme','slate'));
SET @dash2 = LAST_INSERT_ID();

INSERT INTO widgets(dashboard_id,type,title,position_x,position_y,width,height,include_in_main,autorun_on_load,config_json,style_json) VALUES
(@dash2,'data','MRR (Monthly Recurring Revenue)',0,0,3,2,1,1, JSON_OBJECT('webhook_url','https://YOUR_N8N/webhook/mrr','output_schema',JSON_OBJECT('type','kpi')), JSON_OBJECT('icon','dollar-sign','accent','#0d6efd')),
(@dash2,'data','Sales Pipeline',3,0,5,3,1,1, JSON_OBJECT('webhook_url','https://YOUR_N8N/webhook/pipeline','output_schema',JSON_OBJECT('type','chart')), JSON_OBJECT('icon','activity','accent','#198754')),
(@dash2,'data','Web Traffic (7d)',8,0,4,2,1,1, JSON_OBJECT('webhook_url','https://YOUR_N8N/webhook/traffic','output_schema',JSON_OBJECT('type','chart')), JSON_OBJECT('icon','trending-up','accent','#6f42c1')),
(@dash2,'data','Customer Satisfaction (CSAT)',0,2,3,2,1,1, JSON_OBJECT('webhook_url','https://YOUR_N8N/webhook/csat','output_schema',JSON_OBJECT('type','kpi')), JSON_OBJECT('icon','smile','accent','#20c997')),
(@dash2,'data','Top Issues (Support)',3,3,9,2,1,1, JSON_OBJECT('webhook_url','https://YOUR_N8N/webhook/top_issues','output_schema',JSON_OBJECT('type','list')), JSON_OBJECT('icon','alert-circle','accent','#fd7e14'));

-- 3) AI Intake & Ticketing Desk
INSERT INTO dashboards (name, slug, description, created_by, settings_json) VALUES
('AI Intake & Ticketing Desk', 'ai-intake-desk', 'Collect requests, triage with AI, and return a ticket + status trail.', 1, JSON_OBJECT('theme','teal'));
SET @dash3 = LAST_INSERT_ID();

INSERT INTO widgets(dashboard_id,type,title,position_x,position_y,width,height,include_in_main,autorun_on_load,config_json,style_json) VALUES
(@dash3,'app','New Request',0,0,6,3,0,0,
  JSON_OBJECT('webhook_url','https://YOUR_N8N/webhook/new_request',
              'params_schema', JSON_ARRAY(
                JSON_OBJECT('key','summary','label','Summary','type','text','required',true),
                JSON_OBJECT('key','details','label','Details','type','textarea'),
                JSON_OBJECT('key','priority','label','Priority','type','select','options',JSON_ARRAY('Low','Normal','High'))
              ),
              'output_schema', JSON_OBJECT('type','ticket')
  ),
  JSON_OBJECT('icon','inbox','accent','#0d6efd')
),
(@dash3,'data','My Open Tickets',6,0,6,3,1,1,
  JSON_OBJECT('webhook_url','https://YOUR_N8N/webhook/my_tickets','output_schema',JSON_OBJECT('type','table')),
  JSON_OBJECT('icon','list','accent','#198754')
);
