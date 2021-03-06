package org.xidea.lite.servlet;

import java.io.File;
import java.io.IOException;
import java.io.PrintWriter;
import java.lang.reflect.Constructor;
import java.net.URI;
import java.util.Map;
import java.util.regex.Pattern;

import javax.servlet.GenericServlet;
import javax.servlet.ServletConfig;
import javax.servlet.ServletContext;
import javax.servlet.ServletException;
import javax.servlet.ServletRequest;
import javax.servlet.ServletResponse;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.apache.commons.logging.Log;
import org.apache.commons.logging.LogFactory;
import org.xidea.lite.TemplateEngine;
import org.xidea.lite.LiteEngine;
import org.xidea.lite.Template;

public class TemplateServlet extends GenericServlet {
	private static final long serialVersionUID = 1L;
	private static final Log log = LogFactory.getLog(TemplateServlet.class);
	private static final String LOCAL_IP = "^(?:127\\.0\\.0\\.1|10\\..+|172\\.(?:1[6789]|2.|30|31)\\..+|192\\.168\\..+|([0:]+1))$";

	protected TemplateEngine templateEngine;
	protected String serviceBase = "/WEB-INF/service/lite-service";
	private Pattern debug = null;
	private DebugSupport debugService = null;

	@Override
	public void init() throws ServletException {
		final ServletConfig config = this.getServletConfig();
		initEngine(config);
		initDebug(config);

	}

	protected void initEngine(final ServletConfig config)
			throws ServletException {
		ServletContext context = config.getServletContext();
		String configPath = config.getInitParameter("config");
		if (configPath == null) {
			configPath = "/WEB-INF/lite.xml";
		}
		final File root = new File(context.getRealPath("/"));
		final File litecode = new File(root, "WEB-INF/litecode/");
		
		try {
			Class<TemplateEngine> engineClass;
			try {
				engineClass = loadImplClass(config);
			} catch (ClassNotFoundException e) {
				templateEngine = new LiteEngine(litecode.toURI());
				log.info("Lite LiteEngine(runtime only) is used");
				return;
			}
			Constructor<TemplateEngine> constructor;
			try {
				constructor = (Constructor<TemplateEngine>) engineClass
						.getConstructor(URI.class, URI.class, URI.class);
				final File configFile = new File(context
						.getRealPath(configPath));
				templateEngine = constructor.newInstance(root.toURI(),
						configFile.toURI(), litecode.toURI());
				log.info("Lite HotLiteEngine(runtime and compiler) is used");
			} catch (NoSuchMethodException e) {
				log
						.error("TemplateEngine must has a constructor(URI webbase,URI config,URI compilecache)");
				throw e;
			}
		} catch (Exception e) {
			log.error("Lite HotLiteEngine init faild!", e);
			throw new ServletException(e);
		}
	}

	protected Class<TemplateEngine> loadImplClass(final ServletConfig config)
			throws ClassNotFoundException {
		String templateImpl = config.getInitParameter(TemplateEngine.class
				.getName());
		if (templateImpl == null) {
			templateImpl = "org.xidea.lite.impl.HotLiteEngine";
		}
		@SuppressWarnings("unchecked")
		Class<TemplateEngine> engineClass = (Class<TemplateEngine>) Class
				.forName(templateImpl);
		return engineClass;
	}

	protected void initDebug(final ServletConfig config) {
		try {
			String serviceBase = config.getInitParameter("debugService");
			if (serviceBase != null) {
				this.serviceBase = serviceBase;
			}
			String debugModel = config.getInitParameter("debug");
			if (debugModel == null) {
				this.debug = Pattern.compile(LOCAL_IP);
			} else {
				String model = debugModel.trim();
				if ("true".equalsIgnoreCase(model)) {
					this.debug = Pattern.compile(".");
				} else if ("false".equalsIgnoreCase(model)) {
					this.debug = null;
				} else {
					this.debug = Pattern.compile(model);
				}
			}
			if (this.debug == null) {
				log.info("debug support was disabled!!");
			} else {
				Class<?> debugClass = Class
						.forName("org.xidea.lite.servlet.DebugSupportImpl");
				debugService = (DebugSupport) debugClass.getConstructor(
						TemplateServlet.class).newInstance(this);

				log.info("debug on client ip:" + debug.pattern()
						+ "; service base=" + this.serviceBase);
			}
		} catch (Exception e) {
			log.error("DebugSupportImpl init faild!", e);
			debug = null;
		}
	}

	@Override
	public void service(ServletRequest req, ServletResponse resp)
			throws ServletException, IOException {
		HttpServletRequest request = (HttpServletRequest) req;
		HttpServletResponse response = (HttpServletResponse) resp;
		String path = request.getServletPath();
		service(path, request, response);
	}

	protected void service(String path, HttpServletRequest request,
			HttpServletResponse response) throws IOException {
		// ???????????????????????????
		if (debug != null && debug.matcher(request.getRemoteAddr()).find()) {
			if (path.equals(this.serviceBase)) {
				if (debugService.service(request, response)) {
					return;
				}
			} else if (debugService.debug(path, request, response)) {
				return;
			}
		}
		// ????????????????????????
		Template template = templateEngine.getTemplate(path);
		String contentType = template.getContentType();
		response.setContentType(contentType);
		PrintWriter out = response.getWriter();
		template.render(createModel(request), out);
		out.flush();
	}

	protected Map<String, Object> createModel(final HttpServletRequest req) {
		return new RequestSessionMap(req);
	}
}
